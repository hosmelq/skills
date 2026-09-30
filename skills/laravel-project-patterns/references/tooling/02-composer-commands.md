# Connect Composer Commands and Lifecycle Hooks

Use Composer aliases for PHPStan, Pint and Rector, with explicit plugin permissions and application autoloading. Keep lifecycle operations in sequence and retain only hooks for installed integrations.

Merge into `composer.json`:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Database\\Factories\\": "database/factories/",
      "Database\\Seeders\\": "database/seeders/"
    },
    "files": [
      "app/functions.php"
    ]
  },
  "autoload-dev": {
    "psr-4": {
      "Tests\\": "tests/"
    }
  },
  "config": {
    "allow-plugins": {
      "ergebnis/composer-normalize": true,
      "pestphp/pest-plugin": true,
      "php-http/discovery": true,
      "phpstan/extension-installer": true
    },
    "optimize-autoloader": true,
    "preferred-install": "dist",
    "sort-packages": true
  },
  "scripts": {
    "post-update-cmd": [
      "@php artisan vendor:publish --tag=laravel-assets --ansi --force",
      "@composer bump",
      "@composer normalize",
      "@php artisan boost:install --ansi --guidelines --skills --no-interaction"
    ],
    "pre-package-uninstall": [
      "Illuminate\\Foundation\\ComposerScripts::prePackageUninstall"
    ],
    "post-autoload-dump": [
      "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
      "@php artisan package:discover --ansi",
      "@php artisan filament:upgrade"
    ],
    "phpstan": "phpstan analyse --memory-limit=4G",
    "pint": "pint",
    "rector": "rector"
  },
  "scripts-descriptions": {
    "phpstan": "Runs PHPStan analyse.",
    "pint": "Runs Pint.",
    "rector": "Runs Rector."
  }
}
```

`pint` and `rector` mutate by default; callers use `--test` and `--dry-run` for checks. Composer lifecycle hooks may bootstrap the app and publish files.
