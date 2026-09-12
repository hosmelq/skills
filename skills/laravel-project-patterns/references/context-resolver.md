# Executable Context Router

## When To Use

Use this required entrypoint before reading or discovering pattern references.
Inspect the affected live project code to identify paths, operations, and
concerns. Markdown remains the source of truth; `catalog.json` contains routing
metadata only.

The router bounds catalog access. Do not replace it with searches, directory
listings, the map, guessed reference paths, or unrestricted link following.
Live project code can be searched independently. Catalog-maintenance tasks may
inspect the files they need to change; that exception does not apply to ordinary
application work.

## Pattern

Pass the paths relevant to the query together so the resolver can deduplicate
shared contracts and references:

```shell
php /path/to/laravel-project-patterns/scripts/context.php \
  --path=app/Actions/UpdateRecord.php \
  --path=tests/Integration/Actions/UpdateRecordTest.php
```

The resolver infers an operation only when every recognizable filename agrees.
Pass it explicitly when the operation is known, and add only concerns that are
owned by the matched surfaces:

```shell
php /path/to/laravel-project-patterns/scripts/context.php \
  --path=tests/Feature/Http/Controllers/UpdateRecordControllerTest.php \
  --operation=update \
  --concern=delegated-action
```

Use `--list` to discover supported operations, concerns, aliases, owners, and
path rules. The default output contains selected reference paths, required
applicable gates, word counts, hashes, and one compact frontier summary per loaded parent.
One branch can never consume or hide another branch's frontier.

Expand only the parent you need to reveal its immediate children. Use
`--max-options` and `--offset` to page a large branch without loading unrelated
branches:

```shell
php /path/to/laravel-project-patterns/scripts/context.php \
  --path=tests/Feature/Http/Controllers/RecordControllerTest.php \
  --expand=references/tests/Feature/Http/Controllers/README.md \
  --max-options=10
```

Follow the tree one step at a time. Rerun with `--select` for an immediate child
printed by an expanded frontier; repeat earlier selections when walking deeper.
A jump to a non-child fails closed. Multiple `--expand` and interleaved
`--select` values keep every active task surface independently navigable.

```shell
php /path/to/laravel-project-patterns/scripts/context.php \
  --path=tests/Feature/Http/Controllers/RecordControllerTest.php \
  --select=references/tests/Feature/Http/Controllers/actions/README.md \
  --select=references/tests/Feature/Http/Controllers/actions/update.md
```

After the selection is stable, obtain pattern text through `--include-content`;
it emits the exact selected Markdown within the content budget. Do not read
selected pattern files directly. The metadata-only response includes word
counts, not permission to bypass that budget with file reads. Applicable
gate sections returned separately may be read when their checks are needed;
they are not an alternative route to the linked catalog.

Keep the default limits: 12 selected references, 2,400 words of included content,
and 20 options per expanded frontier. Metadata-only results do not enforce the
content word limit. If a selection is too large, narrow its paths, concerns, or
selections and page large frontiers with `--offset`. Do not automatically raise
limits, truncate references, concatenate files, or read the same oversized pack
directly. A different budget requires an explicit task constraint; it is not an
error-recovery shortcut. Use `--format=json` for structured consumers.

Unknown paths, unsupported owner/operation combinations, equal-priority path
matches, traversal segments, missing targets, invalid anchors, and malformed
catalog data return a non-zero exit code with a specific error.
An unsupported project path indicates a catalog limitation. Report the exact
path and error and stop catalog access for that surface. Do not invent a path,
change the project's structure, or browse references manually to make it fit.
Continue independent work only when it does not require the missing guidance.

Run `php scripts/validate.php` after changing routing metadata, links, or
references, and `php scripts/test.php` after changing resolver behavior. They
use no Composer dependency.

Results describe only the supplied paths. Update the query before consulting
references for newly affected paths. Retain applicable guidance already read
instead of reloading it merely because the query changed.

## Related References

- [`SKILL.md`](../SKILL.md)
- [`references/MAP.md`](MAP.md)
- [`references/README.md`](README.md)
