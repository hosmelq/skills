# Analyze PR Workflows as Untrusted Data

Use trusted default-branch configuration to scan PR workflows without executing their scripts. Checkout the PR separately with credentials disabled; restrict action sources and pins, and run the same analysis on main pushes.

```yaml
name: GitHub Actions security analysis

on:
  # The workflow and configuration come from main; PR files are parsed as data only.
  pull_request_target: # zizmor: ignore[dangerous-triggers]
    types: [opened, ready_for_review, reopened, synchronize]
  push:
    branches: [main]

concurrency:
  cancel-in-progress: true
  group: ${{ github.workflow }}-${{ github.event.pull_request.number || github.ref }}

permissions:
  contents: read

jobs:
  zizmor:
    if: github.event_name != 'pull_request_target' || !github.event.pull_request.draft

    name: Run zizmor

    runs-on: ubuntu-latest

    steps:
      - name: Checkout trusted configuration
        uses: actions/checkout@3d3c42e5aac5ba805825da76410c181273ba90b1 # v7
        with:
          path: trusted
          persist-credentials: false

      - name: Checkout pull request
        if: github.event_name == 'pull_request_target'
        uses: actions/checkout@3d3c42e5aac5ba805825da76410c181273ba90b1 # v7
        with:
          allow-unsafe-pr-checkout: true
          path: pull-request
          persist-credentials: false
          ref: ${{ github.event.pull_request.head.sha }}
          repository: ${{ github.event.pull_request.head.repo.full_name }}

      - name: Run zizmor on pull request
        if: github.event_name == 'pull_request_target'
        uses: zizmorcore/zizmor-action@cc914d7f3750a2d13d75c7f184a1060aa0e9d482 # v0.6.4
        with:
          advanced-security: false
          annotations: true
          collect: all
          config: trusted/.github/zizmor.yml
          inputs: pull-request/.github
          min-severity: medium
          persona: auditor
          version: 1.30.1

      - name: Run zizmor on main
        if: github.event_name == 'push'
        uses: zizmorcore/zizmor-action@cc914d7f3750a2d13d75c7f184a1060aa0e9d482 # v0.6.4
        with:
          advanced-security: false
          annotations: true
          collect: all
          config: trusted/.github/zizmor.yml
          inputs: trusted/.github
          min-severity: medium
          persona: auditor
          version: 1.30.1
```

`.github/zizmor.yml`

```yaml
rules:
  forbidden-uses:
    config:
      allow:
        - actions/*
        - jdx/mise-action
        - pullfrog/pullfrog@v0
        - shivammathur/setup-php
        - zizmorcore/zizmor-action
  unpinned-uses:
    config:
      policies:
        pullfrog/pullfrog: ref-pin
        '*': hash-pin
```

Keep setup and scanner configuration trusted. The explicit unsafe-checkout flag is for data-only scanning. The Pullfrog ref-pin exception requires a deliberate project policy.
