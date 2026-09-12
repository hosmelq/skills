"""Verified model download and offline reuse, using only synthetic bytes."""

from contextlib import redirect_stderr
import hashlib
import http.client
import io
import sys
import tempfile
import unittest
from pathlib import Path
from unittest.mock import patch

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

import embedding_runtime as runtime


class StreamResponse:
    def __init__(self, chunks, before_read=None):
        self.chunks = iter(chunks)
        self.before_read = before_read
        self.closed = False

    def __enter__(self):
        return self

    def __exit__(self, *exc):
        self.closed = True

    def read(self, size):
        if self.before_read:
            self.before_read()
        chunk = next(self.chunks, b"")
        if isinstance(chunk, BaseException):
            raise chunk
        return chunk


class ModelDownloadTests(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory()
        self.addCleanup(self.temporary.cleanup)
        self.root = Path(self.temporary.name)
        self.cache = self.root / "cache"
        self.payload = b"synthetic verified model bytes\n" * 100
        self.digest = hashlib.sha256(self.payload).hexdigest()
        self.target = self.cache / "models" / f"{self.digest}.gguf"
        self.constants = patch.multiple(
            runtime,
            MODEL_SHA256=self.digest,
            MODEL_BYTES=len(self.payload),
            MODEL_URL="https://models.example.invalid/fixed.gguf",
        )
        self.constants.start()
        self.addCleanup(self.constants.stop)
        self.transport = patch.object(
            runtime.urllib.request, "urlopen", side_effect=AssertionError("Unexpected network request"),
        )
        self.urlopen = self.transport.start()
        self.addCleanup(self.transport.stop)
        self.stderr = redirect_stderr(io.StringIO())
        self.stderr.__enter__()
        self.addCleanup(self.stderr.__exit__, None, None, None)

    def serve(self, chunks):
        response = StreamResponse(chunks, before_read=lambda: self.assertFalse(self.target.exists()))
        self.urlopen.side_effect = None
        self.urlopen.return_value = response
        return response

    def assert_no_partial_model(self):
        self.assertFalse(self.target.exists())
        self.assertEqual(list(self.cache.rglob("*.gguf")), [])
        self.assertFalse(any(path.name.startswith(".download-") for path in self.cache.rglob("*")))

    def test_first_use_publishes_verified_bytes_then_reuses_them_offline(self):
        response = self.serve([self.payload[:17], self.payload[17:50], self.payload[50:]])
        configured = runtime.configuration(self.cache)
        self.assertEqual(configured, {"model": str(self.target.resolve())})
        self.assertEqual(self.target.read_bytes(), self.payload)
        self.assertTrue(response.closed)
        self.urlopen.assert_called_once()
        self.urlopen.reset_mock()
        self.urlopen.side_effect = AssertionError("Offline reuse must not contact the registry")
        self.assertEqual(runtime.configuration(self.cache), configured)
        self.urlopen.assert_not_called()
        self.assertFalse(any(path.name.startswith(".download-") for path in self.cache.rglob("*")))

    def test_truncated_oversized_and_wrong_hash_downloads_never_publish(self):
        for received in (self.payload[:-1], self.payload + b"extra", b"X" + self.payload[1:]):
            with self.subTest(received_length=len(received)):
                response = self.serve([received[:20], received[20:]])
                with self.assertRaisesRegex(ValueError, "size|SHA-256"):
                    runtime.configuration(self.cache)
                self.assertTrue(response.closed)
                self.assert_no_partial_model()

    def test_timeout_and_incomplete_http_body_remove_partial_data_and_allow_retry(self):
        for error in (TimeoutError("stream timed out"), http.client.IncompleteRead(b"partial")):
            with self.subTest(error=type(error).__name__):
                response = self.serve([self.payload[:20], error])
                with self.assertRaisesRegex(RuntimeError, "download failed"):
                    runtime.configuration(self.cache)
                self.assertTrue(response.closed)
                self.assert_no_partial_model()
        self.serve([self.payload])
        self.assertEqual(runtime.configuration(self.cache), {"model": str(self.target.resolve())})
        self.assertEqual(self.target.read_bytes(), self.payload)

    def test_interruption_during_streaming_also_removes_partial_data(self):
        response = self.serve([self.payload[:20], KeyboardInterrupt()])
        with self.assertRaises(KeyboardInterrupt):
            runtime.configuration(self.cache)
        self.assertTrue(response.closed)
        self.assert_no_partial_model()

    def test_explicit_local_model_uses_no_network(self):
        local = self.root / "local.gguf"
        local.write_bytes(self.payload)
        self.assertEqual(runtime.configuration(self.cache, str(local)), {"model": str(local.resolve())})
        self.urlopen.assert_not_called()
        self.assertFalse(self.target.exists())

    def test_corrupt_cached_or_explicit_models_are_not_silently_reused(self):
        self.target.parent.mkdir(parents=True)
        self.target.write_bytes(b"X" + self.payload[1:])
        with self.assertRaisesRegex(ValueError, "SHA-256 mismatch"):
            runtime.configuration(self.cache)
        local = self.root / "local.gguf"
        local.write_bytes(self.payload[:-1])
        with self.assertRaisesRegex(ValueError, "SHA-256 mismatch"):
            runtime.configuration(self.cache, str(local))
        self.urlopen.assert_not_called()


if __name__ == "__main__":
    unittest.main()
