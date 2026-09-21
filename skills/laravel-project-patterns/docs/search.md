# Bounded Hybrid Search

## When To Use

Use `scripts/search.py` to select descriptions before reading catalog patterns. It combines SQLite FTS5
BM25 and Qwen3-Embedding 0.6B vectors with reciprocal rank fusion. Markdown stays
the source of truth; there is no generative helper or external inference API.

The current references cover controller `create`, `destroy`, `edit`, `index`, `show` and `store` tests. Other areas will be added
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

#### Create

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

#### Destroy

- [Controller Tests: Ordered Destroy Block](../references/tests/controllers/12-destroy-test-order.md)
- [Direct Deletion And Controller Response](../references/tests/controllers/13-destroy-delete-response.md)
- [Scoped Parent And Ancestor Binding](../references/tests/controllers/14-destroy-parent-bindings.md)
- [Target Binding And Independent Tenant Integrity](../references/tests/controllers/15-destroy-record-bindings.md)
- [Policy Access Restrictions](../references/tests/controllers/16-destroy-access-rules.md)
- [History Assignment And Required Initial Errors](../references/tests/controllers/17-destroy-domain-errors.md)
- [Live And Soft Deleted Dependencies](../references/tests/controllers/18-destroy-dependency-errors.md)
- [Action Lifecycle Errors](../references/tests/controllers/19-destroy-lifecycle-errors.md)
- [Nested Success Redirects And Default Record](../references/tests/controllers/20-destroy-nested-delete-response.md)
- [Reactivating A Deactivated Record](../references/tests/controllers/21-destroy-reactivation.md)

#### Edit

- [Ordered edit block](../references/tests/controllers/22-edit-test-order.md)
- [Direct page and authentication](../references/tests/controllers/23-edit-page-response.md)
- [Scoped parent and ancestor binding](../references/tests/controllers/24-edit-parent-bindings.md)
- [Target binding and tenant integrity](../references/tests/controllers/25-edit-record-bindings.md)
- [Inactive record, parent and ancestor](../references/tests/controllers/26-edit-access-rules.md)
- [Full enum options and parent IDs](../references/tests/controllers/27-edit-enums-and-parent-ids.md)
- [Stored dependent options and partial reload](../references/tests/controllers/28-edit-dependent-selects.md)
- [Current live, deleted and inactive relation](../references/tests/controllers/29-edit-selected-relation.md)
- [Current inactive option alongside active choices](../references/tests/controllers/30-edit-selected-option.md)
- [Historical selections and available options](../references/tests/controllers/31-edit-historical-relations.md)
- [Absent and present related rate flags](../references/tests/controllers/32-edit-related-record-flags.md)
- [Final record flag and read-only parent states](../references/tests/controllers/33-edit-final-states.md)

#### Index

- [Ordered index block](../references/tests/controllers/34-index-test-order.md)
- [Authentication and tenant access](../references/tests/controllers/35-index-authentication.md)
- [Scoped parent bindings](../references/tests/controllers/36-index-parent-bindings.md)
- [Page and collection props](../references/tests/controllers/37-index-page-contract.md)
- [Configured collection order](../references/tests/controllers/38-index-configured-order.md)
- [Live record pagination](../references/tests/controllers/39-index-pagination.md)
- [Historical related records](../references/tests/controllers/40-index-historical-relations.md)
- [Pending records in request order](../references/tests/controllers/41-index-pending-records.md)
- [Inactive records and ancestors](../references/tests/controllers/42-index-inactive-records.md)
- [Tenant collection filters](../references/tests/controllers/43-index-tenant-filters.md)
- [Parent collection filters](../references/tests/controllers/44-index-parent-filters.md)
- [Conflicting ownership](../references/tests/controllers/45-index-ownership-integrity.md)
- [Nested ancestor and deletion filters](../references/tests/controllers/46-index-deep-filters.md)

#### Show

- [Ordered show block](../references/tests/controllers/47-show-test-order.md)
- [Settings authentication](../references/tests/controllers/48-show-01-settings-authentication.md)
- [Direct record authentication](../references/tests/controllers/48-show-02-direct-authentication.md)
- [One-parent authentication](../references/tests/controllers/48-show-03-parent-authentication.md)
- [Two-parent authentication](../references/tests/controllers/48-show-04-ancestor-authentication.md)
- [Flat JSON and JSON API responses](../references/tests/controllers/49-show-json-responses.md)
- [Scoped parent bindings](../references/tests/controllers/50-show-parent-bindings.md)
- [Direct record binding](../references/tests/controllers/51-show-01-direct-record-bindings.md)
- [Record binding under one parent](../references/tests/controllers/51-show-02-parent-record-bindings.md)
- [Conflicting record ownership](../references/tests/controllers/51-show-03-ownership-integrity.md)
- [Record binding under two parents](../references/tests/controllers/51-show-04-deep-record-bindings.md)
- [Page and route identifiers](../references/tests/controllers/52-show-page-contract.md)
- [Default and selected relations](../references/tests/controllers/53-show-selected-relations.md)
- [Deleted and inactive selected relations](../references/tests/controllers/54-show-historical-relation.md)
- [Tenant settings](../references/tests/controllers/55-show-settings.md)
- [Public fields and hidden foreign keys](../references/tests/controllers/56-show-public-fields.md)
- [All selected historical relations](../references/tests/controllers/57-show-historical-relations.md)
- [Ordered live child collection](../references/tests/controllers/58-show-child-collection.md)
- [Inactive records and ancestors](../references/tests/controllers/59-show-inactive-records.md)
- [Parent finality and mutation flags](../references/tests/controllers/60-show-mutation-flags.md)

#### Store

- [Ordered store block](../references/tests/controllers/61-store-00-test-order.md)
- [Access Guest Root](../references/tests/controllers/61-store-01-access-guest-root.md)
- [Access Guest Nested](../references/tests/controllers/61-store-02-access-guest-nested.md)
- [Access Existing Record](../references/tests/controllers/61-store-03-access-existing-record.md)
- [Access Unrelated Root](../references/tests/controllers/61-store-04-access-unrelated-root.md)
- [Access Unrelated Nested](../references/tests/controllers/61-store-05-access-unrelated-nested.md)
- [Api Json Resource](../references/tests/controllers/61-store-06-api-json-resource.md)
- [Bindings Existing Record](../references/tests/controllers/61-store-07-bindings-existing-record.md)
- [Bindings Create Parent](../references/tests/controllers/61-store-08-bindings-create-parent.md)
- [Bindings Create Ancestor Chain](../references/tests/controllers/61-store-09-bindings-create-ancestor-chain.md)
- [Bindings Nested Existing Record](../references/tests/controllers/61-store-10-bindings-nested-existing-record.md)
- [Access Inactive State](../references/tests/controllers/61-store-11-access-inactive-state.md)
- [Validation Contact Fields](../references/tests/controllers/61-store-12-validation-contact-fields.md)
- [Validation Address Fields](../references/tests/controllers/61-store-13-validation-address-fields.md)
- [Validation Geographic Fields](../references/tests/controllers/61-store-14-validation-geographic-fields.md)
- [Validation Profile Settings](../references/tests/controllers/61-store-15-validation-profile-settings.md)
- [Validation Reference And Measures](../references/tests/controllers/61-store-16-validation-reference-and-measures.md)
- [Validation Line Value And Measures](../references/tests/controllers/61-store-17-validation-line-value-and-measures.md)
- [Validation Assignment Id](../references/tests/controllers/61-store-18-validation-assignment-id.md)
- [Validation Editable Label Fields](../references/tests/controllers/61-store-19-validation-editable-label-fields.md)
- [Validation State Fields](../references/tests/controllers/61-store-20-validation-state-fields.md)
- [Validation Transit Bounds](../references/tests/controllers/61-store-21-validation-transit-bounds.md)
- [Validation Rounding Rule](../references/tests/controllers/61-store-22-validation-rounding-rule.md)
- [Validation Interval Rate](../references/tests/controllers/61-store-23-validation-interval-rate.md)
- [Relations Owner And Assignment](../references/tests/controllers/61-store-24-relations-owner-and-assignment.md)
- [Relations Service And Rule](../references/tests/controllers/61-store-25-relations-service-and-rule.md)
- [Relations Facility Roles](../references/tests/controllers/61-store-26-relations-facility-roles.md)
- [Relations Status Selection](../references/tests/controllers/61-store-27-relations-status-selection.md)
- [Relations Item Group](../references/tests/controllers/61-store-28-relations-item-group.md)
- [Uniqueness Email](../references/tests/controllers/61-store-29-uniqueness-email.md)
- [Uniqueness Phone](../references/tests/controllers/61-store-30-uniqueness-phone.md)
- [Uniqueness Name With Minimal Success](../references/tests/controllers/61-store-31-uniqueness-name-with-minimal-success.md)
- [Uniqueness Name With Mapped Success](../references/tests/controllers/61-store-32-uniqueness-name-with-mapped-success.md)
- [Uniqueness Name With Required Fields](../references/tests/controllers/61-store-33-uniqueness-name-with-required-fields.md)
- [Uniqueness Parent Scoped Code](../references/tests/controllers/61-store-34-uniqueness-parent-scoped-code.md)
- [Failures Assignment](../references/tests/controllers/61-store-35-failures-assignment.md)
- [Failures Inactive Parent](../references/tests/controllers/61-store-36-failures-inactive-parent.md)
- [Failures Range Collision](../references/tests/controllers/61-store-37-failures-range-collision.md)
- [Failures Final Parent And Group](../references/tests/controllers/61-store-38-failures-final-parent-and-group.md)
- [Failures State And Reference](../references/tests/controllers/61-store-39-failures-state-and-reference.md)
- [Failures Related Prerequisites](../references/tests/controllers/61-store-40-failures-related-prerequisites.md)
- [Failures Facility Availability](../references/tests/controllers/61-store-41-failures-facility-availability.md)
- [Failures Related Availability](../references/tests/controllers/61-store-42-failures-related-availability.md)
- [Failures Deactivation Guards](../references/tests/controllers/61-store-43-failures-deactivation-guards.md)
- [Mapping Current Actor](../references/tests/controllers/61-store-44-mapping-current-actor.md)
- [Mapping Scalar Input](../references/tests/controllers/61-store-45-mapping-scalar-input.md)
- [Mapping Editable Fields](../references/tests/controllers/61-store-46-mapping-editable-fields.md)
- [Mapping Enums And Boolean](../references/tests/controllers/61-store-47-mapping-enums-and-boolean.md)
- [Mapping Seeded Province](../references/tests/controllers/61-store-48-mapping-seeded-province.md)
- [Mapping Nested Related Id](../references/tests/controllers/61-store-49-mapping-nested-related-id.md)
- [Mapping Root Related Id](../references/tests/controllers/61-store-50-mapping-root-related-id.md)
- [Mapping Nullable Input](../references/tests/controllers/61-store-51-mapping-nullable-input.md)
- [Mapping Decimal And Rounding](../references/tests/controllers/61-store-52-mapping-decimal-and-rounding.md)
- [Mapping Optional Bound](../references/tests/controllers/61-store-53-mapping-optional-bound.md)
- [Responses Deactivation](../references/tests/controllers/61-store-54-responses-deactivation.md)

## Related References

- [Skill entrypoint](../SKILL.md)
- [Benchmark note](benchmark.md)
