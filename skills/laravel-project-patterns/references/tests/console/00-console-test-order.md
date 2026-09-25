# Console Tests: Ordered Contract Checklist

Feature tests for a command downloading a gzip-compressed SQLite database: initial download, existing target, forced replacement, then malformed replacement.

1. [downloads and extracts the database](01-database-download.md)
2. [skips downloading when the database already exists](02-existing-database.md)
3. [downloads and replaces the database when forced](03-forced-download.md)
4. [preserves the existing database when a forced download is malformed](03-forced-download.md)

Keep standalone `it()` declarations in this order. Separate setup, command invocation and assertions with blank lines. Adapt the command, config keys and output text to the inspected contract. Use isolated test-only target paths and block stray HTTP requests. Delete the target before the initial download and at the end of each test.
