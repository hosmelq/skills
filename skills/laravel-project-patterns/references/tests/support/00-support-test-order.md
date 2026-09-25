# Support Tests: Ordered Contract Checklist

Integration tests for configured media-library helpers: UUID filenames, then relative paths without and with a prefix.

1. [uses a uuid file name when media is added](01-media-file-name.md)
2. [uses the media uuid as the base path](02-media-path.md)
3. [includes the configured prefix before the media uuid path](02-media-path.md)

Keep standalone `it()` declarations in this order. Separate setup, operation and assertions with blank lines. Use a migrated support model implementing `HasMedia` with `InteractsWithMedia`, and the configured custom file namer/path generator. Fake the configured media disk; these examples use `public`.
