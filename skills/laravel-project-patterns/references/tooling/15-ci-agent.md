# Run a Manually Dispatched CI Agent

Use this Pullfrog workflow only with its configured OIDC integration. Manual name/prompt inputs start an independent run, install PHP dependencies and start PostgreSQL. Keep the prompt as an action input and scope token permissions to this job.

```yaml
name: Pullfrog

run-name: ${{ inputs.name || github.workflow }}

on:
  workflow_dispatch:
    inputs:
      name:
        type: string
        description: Run name
      prompt:
        type: string
        description: Agent prompt

concurrency:
  cancel-in-progress: false
  group: ${{ github.workflow }}-${{ github.run_id }}

permissions:
  contents: read

env:
  PHP_VERSION: '8.5'

jobs:
  pullfrog:
    name: Pullfrog

    environment: package-registries

    runs-on: ubuntu-latest

    permissions:
      contents: read
      id-token: write # mint the OIDC token Pullfrog exchanges for its credentials

    steps:
      - name: Checkout code
        uses: actions/checkout@3d3c42e5aac5ba805825da76410c181273ba90b1 # v7
        with:
          fetch-depth: 1
          persist-credentials: false

      - name: Setup Mise
        uses: jdx/mise-action@c2a87611a18de5b3828c5652fe268e992400cb5c # v4.3.0
        with:
          install_args: node nub

      - name: Setup PHP
        uses: shivammathur/setup-php@b604ade2a87db23f8871b7182e69ec5e75effb45
        with:
          coverage: none
          extensions: bcmath, mbstring, pdo_pgsql, zip
          php-version: ${{ env.PHP_VERSION }}
          tools: composer

      - name: Install PHP dependencies
        env:
          COMPOSER_AUTH: ${{ secrets.COMPOSER_AUTH }}
        run: composer install --no-interaction --prefer-dist

      - name: Start PostgreSQL
        env:
          COMPOSE_DB_PORT: '5432'
        run: docker compose up --detach --wait postgresql

      - name: Run agent
        uses: pullfrog/pullfrog@v0
        env:
          MISE_DISABLE_TOOLS: php
          MISE_TASK_RUN_AUTO_INSTALL: 'false'
        with:
          prompt: ${{ inputs.prompt }}
```

`id-token: write` permits OIDC issuance; external credentials remain the integration’s contract. The mutable `v0` ref is an explicit action-policy exception.
