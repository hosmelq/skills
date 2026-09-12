"""Embed with the pinned GGUF directly inside the Python command."""

from contextlib import AbstractContextManager
import ctypes
import hashlib
import http.client
import json
import logging
from pathlib import Path
import sys
import tempfile
import urllib.request

import numpy as np

MODEL_SHA256 = "06507c7b42688469c4e7298b0a1e16deff06caf291cf0a5b278c308249c3e439"
MODEL_BYTES = 639150592
MODEL_URL = f"https://registry.ollama.ai/v2/library/qwen3-embedding/blobs/sha256:{MODEL_SHA256}"
CONTEXT_SIZE = 4096
MODEL_OPTIONS = {
    "embeddings": True,
    "n_ctx": CONTEXT_SIZE,
    "n_batch": CONTEXT_SIZE,
    "n_ubatch": 1024,
    "n_threads": 6,
    "n_threads_batch": 6,
    "n_seq_max": 1,
    "kv_unified": True,
    "offload_kqv": False,
    "op_offload": False,
}


def file_sha256(path):
    digest = hashlib.sha256()
    with Path(path).open("rb") as source:
        for block in iter(lambda: source.read(1024 * 1024), b""):
            digest.update(block)
    return digest.hexdigest()


def download_model(target):
    """Publish only a complete, verified download on the cache filesystem."""
    target.parent.mkdir(parents=True, exist_ok=True)
    print("search: downloading Qwen3-Embedding 0.6B (639 MB) to the local cache...", file=sys.stderr, flush=True)
    with tempfile.TemporaryDirectory(prefix=".download-", dir=target.parent) as directory:
        partial = Path(directory) / "model.gguf"
        digest = hashlib.sha256()
        received = 0
        try:
            with urllib.request.urlopen(MODEL_URL, timeout=60) as response, partial.open("wb") as output:
                for block in iter(lambda: response.read(1024 * 1024), b""):
                    received += len(block)
                    if received > MODEL_BYTES:
                        raise ValueError("Model download exceeds its expected size; nothing was installed")
                    digest.update(block)
                    output.write(block)
        except (OSError, http.client.HTTPException) as error:
            raise RuntimeError(f"Model download failed; check your connection and rerun the command: {error}") from error
        if received != MODEL_BYTES or digest.hexdigest() != MODEL_SHA256:
            raise ValueError("Model download size or SHA-256 mismatch; nothing was installed. Rerun the command to retry.")
        partial.replace(target)
    print("search: model downloaded and SHA-256 verified", file=sys.stderr, flush=True)


def configuration(cache_dir, model=None):
    path = Path(cache_dir) / "runtime.json"
    saved = json.loads(path.read_text()) if path.exists() and model is None else {}
    if not isinstance(saved, dict) or (saved and (set(saved) != {"model"} or not isinstance(saved["model"], str))):
        raise ValueError("Invalid runtime.json; configure with index --model /path/to/model.gguf")
    default_model = (Path(cache_dir) / "models" / f"{MODEL_SHA256}.gguf").resolve()
    model_path = Path(model or saved.get("model") or default_model).expanduser().resolve()
    if model_path == default_model and not model_path.exists():
        download_model(model_path)
        return {"model": str(model_path)}
    model_path = model_path.resolve(strict=True)
    if not model_path.is_file():
        raise ValueError("The configured GGUF must be a file")
    if file_sha256(model_path) != MODEL_SHA256:
        raise ValueError("Model SHA-256 mismatch: use the pinned Qwen3-Embedding 0.6B Q8_0 GGUF from the search guide")
    return {"model": str(model_path)}


class EmbeddingRuntime(AbstractContextManager):
    """Own one CPU model through llama-cpp-python's public native bindings."""

    def __init__(self, config):
        self.config = config
        self.api = None
        self.model = self.context = self.batch = None
        self.log_level = None
        self.fingerprint = {}
        self.input_tokens = 0

    def __enter__(self):
        import llama_cpp

        self.api = api = llama_cpp
        logger = logging.getLogger("llama-cpp-python")
        self.log_level = logger.level
        logger.setLevel(logging.ERROR)
        try:
            api.llama_backend_init()
            params = api.llama_model_default_params()
            # An empty native device list selects CPU even in Metal-enabled wheels.
            self.devices = (ctypes.c_void_p * 1)(None)
            params.devices = ctypes.cast(self.devices, ctypes.c_void_p)
            params.n_gpu_layers = 0
            self.model = api.llama_model_load_from_file(self.config["model"].encode(), params)
            if not self.model:
                raise RuntimeError("Failed to load the local embedding model")
            if api.llama_model_n_embd_out(self.model) != 1024:
                raise RuntimeError("The embedding model must provide 1024 dimensions")
            context_params = api.llama_context_default_params()
            for key, value in MODEL_OPTIONS.items():
                setattr(context_params, key, value)
            context_params.pooling_type = api.LLAMA_POOLING_TYPE_LAST
            context_params.flash_attn_type = api.LLAMA_FLASH_ATTN_TYPE_DISABLED
            self.context = api.llama_init_from_model(self.model, context_params)
            if not self.context:
                raise RuntimeError("Failed to create the CPU embedding context")
            self.batch = api.llama_batch_init(CONTEXT_SIZE, 0, 1)
            self.vocab = api.llama_model_get_vocab(self.model)
            self.fingerprint = {
                "model_sha256": MODEL_SHA256,
                "dimensions": 1024,
                "runtime": "llama-cpp-python-native",
                "version": api.__version__,
                "library_sha256": file_sha256(api.llama_cpp._lib._name),
                "build": api.llama_print_system_info().decode(),
                "pooling": "last",
                "device": "cpu",
                "flash_attention": False,
                "add_special": True,
                "parse_special": False,
                "options": MODEL_OPTIONS,
            }
            return self
        except BaseException:
            self.close()
            raise

    def tokenize(self, text):
        encoded = text.encode("utf-8")
        tokens = (self.api.llama_token * CONTEXT_SIZE)()
        count = self.api.llama_tokenize(self.vocab, encoded, len(encoded), tokens, CONTEXT_SIZE, True, False)
        if not 0 < count < CONTEXT_SIZE:
            raise ValueError(f"Embedding input has {abs(count)} native tokens; narrow it below {CONTEXT_SIZE}. No text was truncated.")
        return list(tokens[:count])

    def __call__(self, texts):
        if not self.context:
            raise RuntimeError("The embedding model is not open")
        if not isinstance(texts, list) or not texts or any(not isinstance(text, str) or not text for text in texts):
            raise ValueError("Embedding input must be a nonempty list of nonempty strings")
        tokenized = [self.tokenize(text) for text in texts]
        vectors = []
        memory = self.api.llama_get_memory(self.context)
        try:
            for tokens in tokenized:
                self.api.llama_memory_clear(memory, True)
                self.batch.n_tokens = len(tokens)
                for position, token in enumerate(tokens):
                    self.batch.token[position] = token
                    self.batch.pos[position] = position
                    self.batch.n_seq_id[position] = 1
                    self.batch.seq_id[position][0] = 0
                    self.batch.logits[position] = True
                if self.api.llama_decode(self.context, self.batch) != 0:
                    raise RuntimeError("The CPU embedding evaluation failed")
                pointer = self.api.llama_get_embeddings_seq(self.context, 0)
                if not pointer:
                    raise RuntimeError("The model returned no pooled embedding")
                vectors.append(list(pointer[:1024]))
        finally:
            self.api.llama_memory_clear(memory, True)
        result = np.asarray(vectors, dtype=np.float32)
        if result.shape != (len(texts), 1024) or not np.isfinite(result).all():
            raise RuntimeError("The model returned invalid embedding dimensions or values")
        self.input_tokens += sum(map(len, tokenized))
        return result

    def close(self):
        if self.batch is not None:
            self.api.llama_batch_free(self.batch)
            self.batch = None
        if self.context:
            self.api.llama_free(self.context)
            self.context = None
        if self.model:
            self.api.llama_model_free(self.model)
            self.model = None
        if self.log_level is not None:
            logging.getLogger("llama-cpp-python").setLevel(self.log_level)
            self.log_level = None

    def __exit__(self, *exc):
        self.close()
