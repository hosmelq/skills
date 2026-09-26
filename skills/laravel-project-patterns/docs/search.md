# Bounded Hybrid Search

Use `scripts/search.py` for bounded retrieval of model and enum implementation patterns and tests. It ranks Markdown with SQLite FTS5 BM25, Qwen3-Embedding 0.6B and reciprocal rank fusion; returned candidates establish applicability only for their stated contracts.

## Local Setup

Requires macOS or Linux, `uv`, and a C/C++ compiler for the first dependency
build. The first `search` automatically downloads the 639 MB
[Qwen3-Embedding 0.6B Q8_0 GGUF](https://registry.ollama.ai/v2/library/qwen3-embedding/blobs/sha256:06507c7b42688469c4e7298b0a1e16deff06caf291cf0a5b278c308249c3e439),
verifies its size and SHA-256, and builds the local index before searching.

To prepare the model and index ahead of a task, optionally run:

```shell
uv run <skill-directory>/scripts/search.py index
```

`uv` installs the pinned Python dependencies on first use, including
`llama-cpp-python`, which runs the downloaded model inside Python. The model,
configuration and derived index live in `<skill-directory>/.cache`, excluded from Git. Put
`--cache-dir=/another/writable/directory` before the subcommand when the skill
installation is read-only; use that same directory for subsequent commands.

Subsequent runs reuse the model without downloading it again. For an existing
verified GGUF, `index --model=/path/to/model.gguf` skips the download. A failed
or interrupted download never installs a partial model; rerun the command to
retry. Network access is needed for initial dependencies and model download.

Search and indexing load the model; reads do not. Commands sharing a cache run serially to limit memory use. Model weights and the index are not shipped in Git.

## Query The Actual Contract

Write task JSON outside tracked content with only relevant inspected facts.

```json
{
  "request": "Test the create form's dependent country and province selects.",
  "paths": ["app/Http/Controllers/FacilityController.php", "tests/Feature/Http/Controllers/FacilityControllerTest.php"],
  "code_context": {
    "controller": "The Inertia create page accepts country_code, exposes countryCode and returns that country's provinces as label/value options through a partial reload.",
    "test_setup": "Nearby tests use login(team: ...) and assertInertia."
  }
}
```

```shell
uv run <skill-directory>/scripts/search.py search --task-file=/path/to/task.json --session=/path/to/task-session.json
uv run <skill-directory>/scripts/search.py read --session=/path/to/task-session.json --ids <selected-id> <another-id>
```

`--task-file=-` accepts JSON on stdin. Ranking uses `request` and `code_context`; paths supply context but do not route by directory. Task text and project code are not stored in the index or session. Only supplied facts and catalog text reach the local model.

Search returns five ranked candidates by default (`--limit=1..10`). New IDs include
title, applicability summary, source-token cost and `read`. Previously described
IDs emit only `id` and the current `read` status, preserving rank without repeating
metadata. Reuse descriptions and examples already in context before searching.
Descriptions are the first paragraph after each reference's H1, limited to 80
`o200k_base` tokens. Keep conditions discriminating; do not replace them with a
keyword list. Selection uses the live contract, not just the rank. Missing
requirements need focused follow-up searches with the same session.

Read accepts selected IDs and emits their complete Markdown literally, each
preceded by `=== <id> <path> ===`, followed by a final `Receipt: {...}` line.
It loads no embedding model and does not follow links. The sum of source sections,
including ID/path headings and separators, must fit `--budget` (default and
ceiling: 4,000 tokens per response). A source that does not fit appears in
`blocked` with its cost; it is not truncated, marked read or replaced by a
lower-ranked source. Read blocked
IDs in another batch. A single source larger than 4,000 needs a maintainer to
split it into self-contained examples before it can be retrieved.

Session files store catalog identity, candidate paths/hashes, read receipts and
cumulative source-token cost, never query text or source bodies. Use a distinct
temporary file per task and consuming agent, outside the skill. An unchanged
source is returned once; later reads report its ID in `already_read`. `--repeat`
explicitly rereads selected IDs when their content is no longer in context.
After compaction, receipts do not mean the content is still available. Start a
new session to receive descriptions again; `read --repeat` requires a known ID.
Session format version 2 rejects earlier sessions: use a new temporary file.
Changed/deleted source IDs fail instead of serving stale text; search again to obtain current IDs. Adding another document
does not renumber existing IDs.

`source_tokens` sums the token counts of emitted Markdown source sections;
`session_source_tokens` accumulates those sections, including explicit rereads.
Neither is a whole-task limit or billing measure. Candidate metadata, receipts,
commands, task input and existing context cost extra. Measure complete
responses across all searches and reads when comparing context consumption.

Every search atomically refreshes added, changed or deleted Markdown. Unchanged embeddings are reused; a changed model or runtime fingerprint invalidates them. Failed updates return an error instead of stale guidance. For a damaged cache, move `.cache` to Trash and rerun search.

## Maintain The Skill

```shell
python3 <skill-directory>/scripts/validate.py
uv run --no-project --with numpy==2.5.3 --with tiktoken==0.14.0 \
  python -B -m unittest discover -s <skill-directory>/scripts/tests -p 'test_*.py'
```

Application work uses bounded search; maintainers may inspect references directly.

## Related References

- [Skill entrypoint](../SKILL.md)
- [Benchmark note](benchmark.md)
