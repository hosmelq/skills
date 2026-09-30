# Share PHP Dependencies and Run Test Shards

Use one Composer artifact producer, independent PHP quality jobs and four Pest shards. Preserve vendor executable modes in a tar artifact. Adapt installed extensions, registry/environment settings and required test fixtures before running this backend pipeline.

```yaml
name: Continuous integration

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
  php-dependencies:
    environment: package-registries
    runs-on: ubuntu-latest
    steps:
      - &checkout
        uses: actions/checkout@3d3c42e5aac5ba805825da76410c181273ba90b1
        with:
          persist-credentials: false
      - &php
        uses: shivammathur/setup-php@b604ade2a87db23f8871b7182e69ec5e75effb45
        with:
          coverage: none
          extensions: bcmath, mbstring, pdo_pgsql, zip
          php-version: ${{ env.PHP_VERSION }}
          tools: composer
      - env:
          COMPOSER_AUTH: ${{ secrets.COMPOSER_AUTH }}
        run: composer install --no-interaction --prefer-dist
      - run: tar -cf "${RUNNER_TEMP}/php-vendor.tar" vendor
      - uses: actions/upload-artifact@043fb46d1a93c77aae656e7c1c64a875d1fc6a0a
        with:
          if-no-files-found: error
          name: php-vendor
          path: ${{ runner.temp }}/php-vendor.tar
          retention-days: 1

  backend-checks:
    needs: php-dependencies
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        command:
          - composer normalize --dry-run
          - composer phpstan
          - composer pint -- --test
          - composer rector -- --dry-run
          - vendor/bin/composer-dependency-analyser
    steps:
      - *checkout
      - *php
      - &vendor
        uses: actions/download-artifact@3e5f45b2cfb9172054b4087a40e8e0b5a5461e7c
        with:
          name: php-vendor
          path: ${{ runner.temp }}
      - &extract
        run: tar -xf "${RUNNER_TEMP}/php-vendor.tar"
      - env:
          CHECK_COMMAND: ${{ matrix.command }}
        run: bash -e -o pipefail -c "$CHECK_COMMAND"

  tests:
    needs: php-dependencies
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        shard: [1, 2, 3, 4]
    steps:
      - *checkout
      - *php
      - *vendor
      - *extract
      - run: |
          echo "::add-matcher::${{ runner.tool_cache }}/php.json"
          echo "::add-matcher::${{ runner.tool_cache }}/phpunit.json"
      - run: |
          cp .env.testing.example .env.testing
          php artisan key:generate --env=testing
      - env:
          COMPOSE_DB_PORT: '5432'
        run: docker compose up --detach --wait postgresql
      - run: vendor/bin/pest --ci --parallel --shard=${{ matrix.shard }}/4
```

Add required fixture setup after `.env.testing` preparation. These jobs contain fixed repository commands; keep full CI test execution separate from local TIA replay.

[Pest CI](https://pestphp.com/docs/continuous-integration) · [Workflow reuse](https://docs.github.com/en/actions/reference/workflows-and-actions/reusing-workflow-configurations)
