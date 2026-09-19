# Bounded Hybrid Search

## When To Use

Use `scripts/search.py` to select descriptions before reading catalog patterns. It combines SQLite FTS5
BM25 and Qwen3-Embedding 0.6B vectors with reciprocal rank fusion. Markdown stays
the source of truth; there is no generative helper or external inference API.

The current references cover controller `create` tests. Other areas will be added
incrementally; a returned reference is not evidence that an unrelated area is covered.

## Pattern

### Local Setup

Requires macOS or Linux, `uv`, and a C/C++ compiler for the first dependency
build. The first `search` automatically downloads the 639 MB
[Qwen3-Embedding 0.6B Q8_0 GGUF](https://registry.ollama.ai/v2/library/qwen3-embedding/blobs/sha256:06507c7b42688469c4e7298b0a1e16deff06caf291cf0a5b278c308249c3e439),
verifies its size and SHA-256, and builds the local index before searching:

```text
06507c7b42688469c4e7298b0a1e16deff06caf291cf0a5b278c308249c3e439
```

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

Indexing and search load the model through the Python binding and release
it afterward; selected reads do not load it. Inference uses six CPU threads and one 4,096-token context;
commands sharing a cache run serially to limit concurrent model memory.
There is no service, port, HTTP inference request or model-manager installation.
On another computer, the first search performs the same setup automatically.
Model weights and the database are never shipped in Git.

### Query The Actual Contract

Write a task JSON file outside tracked project content. Keep inspected code and
facts relevant to the requested behavior; do not send a repository dump.

```json
{
  "request": "Test the create form's dependent country and province selects.",
  "paths": ["src/Facilities/Http/Controllers/FacilityController.php", "tests-new/Feature/Facilities/FacilityControllerTest.php"],
  "code_context": {
    "controller": "The Inertia create page accepts country_code, exposes countryCode and returns that country's provinces as label/value options through a partial reload.",
    "test_setup": "The active PHPUnit configuration selects tests-new; preserve its configured command and existing test naming."
  }
}
```

```shell
uv run <skill-directory>/scripts/search.py search --task-file=/path/to/task.json --session=/path/to/task-session.json
uv run <skill-directory>/scripts/search.py read --session=/path/to/task-session.json --ids <selected-id> <another-id>
```

`--task-file=-` accepts JSON on stdin. Paths identify the current project's files;
they never route by directory names. Ranking uses `request` plus `code_context`
against the complete reference text, combining BM25 and embeddings as before.
Only supplied facts and catalog text reach the local model; task text and project
code are not stored in the index or session.

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

Every search checks current Markdown hashes and updates added, changed or deleted
documents atomically. Unchanged embeddings are reused; a changed model/runtime
fingerprint invalidates them. A failed update returns an error instead of stale
guidance. To recreate a damaged cache, move that installation's `.cache` to Trash
and rerun the command; automatic setup recreates it.

### Maintain The Skill

```shell
python3 <skill-directory>/scripts/validate.py
uv run --no-project --with numpy==2.5.3 --with tiktoken==0.14.0 \
  python -B -m unittest discover -s <skill-directory>/scripts/tests -p 'test_*.py'
```

Keep catalog text out of automatic preload paths. Maintenance may inspect source
files directly; application work retrieves them through the bounded search.

### Catalog Navigation For Maintenance

1. [Route binding and soft-deleted parents](../references/tests/controllers/01-create-route-bindings.md)
2. [Inactive parent access restriction](../references/tests/controllers/02-create-inactive-parent.md)
3. [Positive page contract and enum props](../references/tests/controllers/03-create-page-contract.md)
4. [Ordered eligible options](../references/tests/controllers/04-create-ordered-options.md)
5. [Category options and parent payload](../references/tests/controllers/05-create-category-options.md)
6. [Nested option IDs and metadata](../references/tests/controllers/06-create-nested-option-props.md)
7. [Dependent selects and partial reload](../references/tests/controllers/07-create-dependent-selects.md)
8. [Unavailable related records](../references/tests/controllers/08-create-related-option-filters.md)
9. [Independent option ownership](../references/tests/controllers/09-create-option-ownership.md)
10. [Unavailable options](../references/tests/controllers/10-create-unavailable-options.md)
11. [Read-only final parent states](../references/tests/controllers/11-create-read-only.md)

## Related References

- [Skill entrypoint](../SKILL.md)
- [Benchmark note](benchmark.md)
