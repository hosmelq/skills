# Record and Share a Test Impact Baseline

Use a separate Pest TIA workflow with PCOV, full Git history, a fresh baseline and the testing database. Upload the resolved storage directory including hidden files; ordinary CI checks remain independent of cached results.

`.github/workflows/tia-baseline.yml`

```yaml
name: Pest TIA baseline

on:
  push:
    branches: [main]
  workflow_dispatch:

concurrency:
  cancel-in-progress: true
  group: ${{ github.workflow }}-${{ github.ref }}

permissions:
  contents: read

env:
  PHP_VERSION: '8.5'

jobs:
  baseline:
    name: Build Pest TIA baseline

    environment: package-registries

    runs-on: ubuntu-latest

    steps:
      - name: Checkout
        uses: actions/checkout@3d3c42e5aac5ba805825da76410c181273ba90b1 # v7
        with:
          fetch-depth: 0
          persist-credentials: false

      - name: Setup PHP
        uses: shivammathur/setup-php@b604ade2a87db23f8871b7182e69ec5e75effb45
        with:
          coverage: pcov
          extensions: bcmath, mbstring, pdo_pgsql, zip
          php-version: ${{ env.PHP_VERSION }}
          tools: composer

      - name: Install PHP dependencies
        env:
          COMPOSER_AUTH: ${{ secrets.COMPOSER_AUTH }}
        run: composer install --no-interaction --prefer-dist

      - name: Setup problem matchers
        run: |
          echo "::add-matcher::${{ runner.tool_cache }}/php.json"
          echo "::add-matcher::${{ runner.tool_cache }}/phpunit.json"

      - name: Setup testing environment
        run: |
          cp .env.testing.example .env.testing
          php artisan key:generate --env=testing

      - name: Start PostgreSQL
        env:
          COMPOSE_DB_PORT: '5432'
        run: docker compose up --detach --wait postgresql

      - name: Build Pest TIA baseline
        run: vendor/bin/pest --ci --fresh --tia

      - name: Resolve Pest TIA baseline path
        id: baseline
        run: echo "path=$(./vendor/bin/pest --baseline)" >> "$GITHUB_OUTPUT"

      - name: Upload Pest TIA baseline
        uses: actions/upload-artifact@043fb46d1a93c77aae656e7c1c64a875d1fc6a0a # v7
        with:
          include-hidden-files: true
          if-no-files-found: error
          name: pest-tia-baseline
          path: ${{ steps.baseline.outputs.path }}
          retention-days: 7
```

`tests/Pest.php`

```php
<?php

declare(strict_types=1);

pest()->tia()->baselined()->locally();
```

Prepare required external test fixtures before recording. Local fetching needs authenticated `gh`, a GitHub `origin` remote and the artifact name `pest-tia-baseline`. `baselined()` uses `tia-baseline.yml`; pass another workflow filename explicitly when needed. Pest validates compatibility before reuse.

[Pest TIA](https://pestphp.com/docs/tia)
