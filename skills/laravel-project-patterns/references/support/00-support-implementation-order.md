# Support: Implementation Order

Choose the needed helper:

1. [Media file names](01-media-file-name.md): UUID original basenames.
2. [Media paths](02-media-path.md): UUID directory with an optional prefix.
3. [Canonical Sqids](03-sqid-codec.md): single-number codec with exact re-encoding.
4. [String translations](04-translation-helper.md): typed translation wrapper.
5. [Toast flash data](05-toast-flash.md): global helper and shared enum payload.
6. [Redirect toast macro](06-redirect-toast.md): fluent controller redirects.

The [support test checklist](../tests/support/00-support-test-order.md) covers media attachment behavior.

APIs: [Sqids](https://sqids.org/php), [media naming](https://spatie.be/docs/laravel-medialibrary/v11/advanced-usage/naming-files), [media paths](https://spatie.be/docs/laravel-medialibrary/v11/advanced-usage/using-a-custom-directory-structure), [PHP assertions](https://www.php.net/manual/en/function.assert.php).
