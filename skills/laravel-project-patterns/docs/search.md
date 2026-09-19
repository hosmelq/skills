# Bounded Hybrid Search

## When To Use

Use `scripts/search.py` before reading catalog patterns. It combines SQLite FTS5
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

Each command loads the model directly through the Python binding and releases
it afterward. Inference uses six CPU threads and one 4,096-token context;
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
uv run <skill-directory>/scripts/search.py search --budget=3200 --task-file=/path/to/task.json
```

`--task-file=-` accepts JSON on stdin. `paths` records actual task locations; it
does not map them onto catalog directories or filter by a filename guess. Ranking
uses `request` plus `code_context`, so include the evidenced framework, behavior
owner and relevant module-specific conditions there. Only the supplied facts and
catalog text reach the in-process model; project code is not indexed or
stored in the catalog database.

The JSON response contains complete candidate sources, their cumulative token
count and the number that did not fit. The command above requests 3,200
`o200k_base` tokens per search for the current create references. This does not
limit a whole task: measure complete responses and repeated references across
follow-up queries. Recheck coverage and cumulative cost as the catalog changes.
The CLI default and ceiling remain 4,000; `--budget` may lower it.
Metadata and the caller's existing
context are additional. A source too large for the remaining budget is skipped
whole, never silently truncated. Returned links do not load their targets.

Every query checks current Markdown hashes and updates added, changed or deleted
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

## Related References

- [Skill entrypoint](../SKILL.md)
- [Benchmark note](benchmark.md)
