"""Native binding boundary tests with no model loading or quality claims."""

import ctypes
import json
import logging
import sys
import tempfile
import types
import unittest
from pathlib import Path
from unittest.mock import Mock, patch

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

import embedding_runtime as runtime


class FakeAPI(types.ModuleType):
    """Reuse one native output buffer so tests detect missing vector copies."""

    def __init__(self, library):
        super().__init__("llama_cpp")
        self.__version__ = "fixture-version"
        self.llama_cpp = types.SimpleNamespace(_lib=types.SimpleNamespace(_name=str(library)))
        self.LLAMA_POOLING_TYPE_LAST = 3
        self.LLAMA_FLASH_ATTN_TYPE_DISABLED = 0
        self.llama_token = ctypes.c_int32
        self.output = (ctypes.c_float * 1024)()
        self.decoded = []
        self.freed = []
        self.nonfinite = False
        self.decode_code = 0
        self.llama_backend_init = Mock()
        self.llama_model_default_params = types.SimpleNamespace
        self.llama_context_default_params = types.SimpleNamespace
        self.llama_model_load_from_file = Mock(return_value=11)
        self.llama_model_n_embd_out = Mock(return_value=1024)
        self.llama_init_from_model = Mock(return_value=22)
        self.llama_model_get_vocab = Mock(return_value=33)
        self.llama_get_memory = Mock(return_value=44)
        self.llama_print_system_info = lambda: b"fixture build"
        self.llama_decode = Mock(side_effect=self.decode)
        self.llama_get_embeddings_seq = Mock(return_value=self.output)
        self.llama_memory_clear = Mock(side_effect=self.clear_memory)
        self.llama_batch_free = Mock(side_effect=lambda batch: self.freed.append("batch"))
        self.llama_free = Mock(side_effect=lambda context: self.freed.append("context"))
        self.llama_model_free = Mock(side_effect=lambda model: self.freed.append("model"))

    @staticmethod
    def llama_batch_init(capacity, dimensions, sequences):
        return types.SimpleNamespace(
            n_tokens=0,
            token=(ctypes.c_int32 * capacity)(),
            pos=(ctypes.c_int32 * capacity)(),
            n_seq_id=(ctypes.c_int32 * capacity)(),
            seq_id=[(ctypes.c_int32 * 1)() for _ in range(capacity)],
            logits=(ctypes.c_bool * capacity)(),
        )

    @staticmethod
    def llama_tokenize(vocab, data, length, output, capacity, add_special, parse_special):
        if length > capacity:
            return -length
        for index, value in enumerate(data):
            output[index] = value
        return length

    def clear_memory(self, memory, data):
        for index in range(1024):
            self.output[index] = 0

    def decode(self, context, batch):
        self.decoded.append(list(batch.token[:batch.n_tokens]))
        self.output[0] = float("nan") if self.nonfinite else batch.n_tokens
        self.output[1] = 1
        return self.decode_code


class EmbeddingRuntimeTests(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory()
        self.addCleanup(self.temporary.cleanup)
        self.root = Path(self.temporary.name)
        self.model_path = self.root / "fixture.gguf"
        self.model_path.write_bytes(b"test fixture, not model weights")
        self.library = self.root / "fixture-library"
        self.library.write_bytes(b"test fixture, not a native library")
        self.api = FakeAPI(self.library)
        self.module_patch = patch.dict(sys.modules, {"llama_cpp": self.api})
        self.module_patch.start()
        self.addCleanup(self.module_patch.stop)

    def open_runtime(self):
        return runtime.EmbeddingRuntime({"model": str(self.model_path)})

    def test_long_text_passes_whole_and_outputs_survive_reused_native_memory(self):
        text = "x" * 1040
        with self.open_runtime() as embedding:
            vectors = embedding([text, "second document"])
            self.assertEqual(self.api.decoded, [list(text.encode()), list(b"second document")])
            self.assertEqual(vectors.shape, (2, 1024))
            self.assertEqual(vectors[:, 0].tolist(), [1040.0, 15.0])
            self.assertEqual(embedding.input_tokens, 1055)
            self.assertEqual(self.api.output[0], 0)
        self.assertEqual(self.api.freed, ["batch", "context", "model"])

    def test_oversized_input_rejects_the_entire_batch_before_decode(self):
        for length in (4096, 4097):
            with self.subTest(length=length):
                with self.open_runtime() as embedding:
                    with self.assertRaisesRegex(ValueError, "No text was truncated"):
                        embedding(["acceptable source", "x" * length])
                    self.api.llama_decode.assert_not_called()
                    self.assertEqual(embedding.input_tokens, 0)

    def test_decode_failure_clears_memory_and_releases_resources_once(self):
        self.api.decode_code = -1
        logger = logging.getLogger("llama-cpp-python")
        previous_level = logger.level
        with self.assertRaisesRegex(RuntimeError, "evaluation failed"):
            with self.open_runtime() as embedding:
                embedding(["source"])
        self.assertEqual(self.api.output[0], 0)
        self.assertEqual(embedding.input_tokens, 0)
        self.assertEqual(self.api.freed, ["batch", "context", "model"])
        self.assertEqual(logger.level, previous_level)
        embedding.close()
        self.assertEqual(self.api.freed, ["batch", "context", "model"])
        with self.assertRaisesRegex(RuntimeError, "not open"):
            embedding(["source"])

    def test_positive_decode_status_cannot_be_treated_as_success(self):
        self.api.decode_code = 1
        with self.open_runtime() as embedding:
            with self.assertRaisesRegex(RuntimeError, "evaluation failed"):
                embedding(["source"])
            self.assertEqual(embedding.input_tokens, 0)

    def test_missing_or_nonfinite_pooled_vector_fails_without_retaining_memory(self):
        for failure in ("missing", "nonfinite"):
            with self.subTest(failure=failure):
                api = FakeAPI(self.library)
                if failure == "missing":
                    api.llama_get_embeddings_seq.return_value = None
                else:
                    api.nonfinite = True
                with patch.dict(sys.modules, {"llama_cpp": api}):
                    with self.open_runtime() as embedding:
                        with self.assertRaisesRegex(RuntimeError, "no pooled embedding|invalid embedding"):
                            embedding(["source"])
                        self.assertEqual(api.output[0], 0)
                        self.assertEqual(embedding.input_tokens, 0)
                self.assertEqual(api.freed, ["batch", "context", "model"])

    def test_initialization_failure_frees_only_acquired_native_resources(self):
        for failure, released in (("model", []), ("dimensions", ["model"]), ("context", ["model"])):
            with self.subTest(failure=failure):
                api = FakeAPI(self.library)
                if failure == "model":
                    api.llama_model_load_from_file.return_value = None
                elif failure == "dimensions":
                    api.llama_model_n_embd_out.return_value = 32
                else:
                    api.llama_init_from_model.return_value = None
                logger = logging.getLogger("llama-cpp-python")
                previous_level = logger.level
                with patch.dict(sys.modules, {"llama_cpp": api}):
                    with self.assertRaises(RuntimeError):
                        with self.open_runtime():
                            self.fail("Invalid native resources must prevent entering the runtime")
                self.assertEqual(api.freed, released)
                self.assertEqual(logger.level, previous_level)

    def test_malformed_saved_configuration_fails_with_an_actionable_error(self):
        for saved in ([], None, False, 0, "", ["model"], {"model": 5}, {"model": "x", "server": "y"}):
            with self.subTest(saved=saved):
                (self.root / "runtime.json").write_text(json.dumps(saved))
                with self.assertRaisesRegex(ValueError, "index --model"):
                    runtime.configuration(self.root)
        self.api.llama_model_load_from_file.assert_not_called()

    def test_explicit_model_repairs_saved_configuration_only_after_hash_validation(self):
        (self.root / "runtime.json").write_text("not valid JSON")
        with self.assertRaisesRegex(ValueError, "SHA-256 mismatch"):
            runtime.configuration(self.root, str(self.model_path))
        with patch.object(runtime, "file_sha256", return_value=runtime.MODEL_SHA256):
            self.assertEqual(
                runtime.configuration(self.root, str(self.model_path)),
                {"model": str(self.model_path.resolve())},
            )
        self.api.llama_model_load_from_file.assert_not_called()


if __name__ == "__main__":
    unittest.main()
