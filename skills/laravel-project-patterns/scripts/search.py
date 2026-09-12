#!/usr/bin/env -S uv run --script
# /// script
# requires-python = ">=3.12"
# dependencies = ["llama-cpp-python==0.3.35", "numpy==2.5.3", "tiktoken==0.14.0"]
# ///
"""Retrieve bounded Markdown evidence using BM25 and local embeddings."""

import argparse
from contextlib import closing
import fcntl
import json
import os
from pathlib import Path
import sys

sys.dont_write_bytecode = True


def task_input(path):
    raw = sys.stdin.read() if path == "-" else Path(path).read_text()
    task = json.loads(raw)
    if not isinstance(task, dict) or set(task) != {"request", "paths", "code_context"}:
        raise ValueError("Task JSON must contain exactly request, paths and code_context")
    if not isinstance(task["request"], str) or not task["request"].strip():
        raise ValueError("request must be nonempty text")
    if not isinstance(task["paths"], list) or not all(isinstance(item, str) and item.strip() for item in task["paths"]):
        raise ValueError("paths must be a list of actual project path strings")
    if not isinstance(task["code_context"], (str, dict, list)) or not task["code_context"]:
        raise ValueError("code_context must contain inspected code or established contract facts")
    return task


def main(argv=None):
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--cache-dir", type=Path, help="Local index/config directory (default: <skill>/.cache)")
    commands = parser.add_subparsers(dest="command", required=True)
    index = commands.add_parser("index", help="Prepare the local model and index changed Markdown")
    index.add_argument("--model", help="Use an existing pinned GGUF instead of the automatic download")
    search = commands.add_parser("search", help="Refresh the index and emit complete candidate references")
    search.add_argument("--task-file", required=True, help="JSON request/paths/code_context file, or - for stdin")
    search.add_argument("--budget", type=int, default=4000, help="Cumulative source tokens, at most 4000")
    args = parser.parse_args(argv)
    if args.command == "search" and not 0 < args.budget <= 4000:
        parser.error("--budget must be between 1 and 4000; narrow the task instead of enlarging catalog context")
    root = Path(__file__).resolve().parent.parent
    cache = (args.cache_dir or root / ".cache").expanduser().resolve()
    os.environ["TIKTOKEN_CACHE_DIR"] = str(cache / "tokenizer")
    from embedding_runtime import EmbeddingRuntime, configuration
    from search_index import SearchIndex
    task = task_input(args.task_file) if args.command == "search" else None
    cache.mkdir(parents=True, exist_ok=True)
    # Serialize commands before loading a model so concurrent agents share the resource budget.
    with (cache / "command.lock").open("a+b") as command_lock:
        fcntl.flock(command_lock, fcntl.LOCK_EX)
        config = configuration(cache, getattr(args, "model", None))
        with EmbeddingRuntime(config) as embedding:
            with closing(SearchIndex(root, cache, embedding, fingerprint=embedding.fingerprint)) as store:
                counts = store.synchronize()
                if args.command == "index":
                    output = {"status": "indexed", **counts}
                else:
                    code = task["code_context"]
                    query = task["request"] + "\n" + (code if isinstance(code, str) else json.dumps(code, ensure_ascii=False, separators=(",", ":")))
                    result = store.pack(store.search(query), budget=args.budget)
                    output = {
                        "status": "candidates" if result["references"] else "no_sources_within_budget",
                        "source_budget": args.budget, **result,
                        "instruction": "Check these candidate rules against the supplied live contract. Ranking does not establish applicability or complete coverage. Reference paths belong to the catalog; retain the project's actual paths and test configuration.",
                    }
            output["embedding_input_tokens"] = embedding.input_tokens
        # Configuration is persisted only after a successful synchronization/search.
        import tempfile
        with tempfile.NamedTemporaryFile(mode="w", dir=cache, prefix="runtime-", suffix=".json", delete=False) as temporary:
            json.dump(config, temporary)
        Path(temporary.name).replace(cache / "runtime.json")
    print(json.dumps(output, ensure_ascii=False, separators=(",", ":")))


if __name__ == "__main__":
    try:
        main()
    except (OSError, ValueError, RuntimeError, KeyError) as error:
        print(f"search: {error}", file=sys.stderr)
        raise SystemExit(1)
