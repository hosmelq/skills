# Update Backend Tools and Dependency Manifests

Use Renovate for Composer, Docker, Actions and Mise, plus regex managers for tool versions outside standard fields. Preserve rule order, image compatibility tags, action-pin exceptions and registry credential references.

```json
{
  "$schema": "https://docs.renovatebot.com/renovate-schema.json",
  "customManagers": [
    {
      "customType": "regex",
      "datasourceTemplate": "github-releases",
      "depNameTemplate": "jdx/mise",
      "extractVersionTemplate": "^v(?<version>.+)$",
      "managerFilePatterns": [
        "mise.toml"
      ],
      "matchStrings": [
        "min_version = \"(?<currentValue>[^\"]+)\""
      ]
    },
    {
      "customType": "regex",
      "datasourceTemplate": "github-releases",
      "depNameTemplate": "zizmorcore/zizmor",
      "extractVersionTemplate": "^v(?<version>.+)$",
      "managerFilePatterns": [
        ".github/workflows/zizmor.yml"
      ],
      "matchStrings": [
        "version: (?<currentValue>[0-9]+\\.[0-9]+\\.[0-9]+)"
      ]
    }
  ],
  "dependencyDashboard": true,
  "enabledManagers": [
    "composer",
    "custom.regex",
    "docker-compose",
    "dockerfile",
    "github-actions",
    "mise"
  ],
  "extends": [
    "config:recommended",
    "group:all",
    "helpers:pinGitHubActionDigests",
    "schedule:weekly"
  ],
  "hostRules": [
    {
      "hostType": "packagist",
      "matchHost": "satis.inertiaui.com",
      "password": "{{ secrets.INERTIAUI_PASSWORD }}",
      "username": "{{ secrets.INERTIAUI_USERNAME }}"
    }
  ],
  "includePaths": [
    ".github/workflows/**",
    "compose.yml",
    "composer.json",
    "infrastructure/docker/**",
    "mise.toml"
  ],
  "minimumReleaseAge": "1 day",
  "packageRules": [
    {
      "matchDatasources": [
        "docker"
      ],
      "matchDepNames": [
        "serversideup/php"
      ],
      "versioning": "regex:^(?<compatibility>\\d+\\.\\d+-(?:cli|frankenphp))-v(?<major>\\d+)\\.(?<minor>\\d+)\\.(?<patch>\\d+)$"
    },
    {
      "matchDepNames": [
        "pullfrog/pullfrog"
      ],
      "matchManagers": [
        "github-actions"
      ],
      "pinDigests": false
    },
    {
      "enabled": false,
      "matchDepNames": [
        "ghcr.io/zizmorcore/zizmor",
        "php"
      ]
    },
    {
      "matchManagers": [
        "composer"
      ],
      "postUpdateOptions": [
        "composerWithAll"
      ]
    },
    {
      "matchManagers": [
        "composer"
      ],
      "rangeStrategy": "bump"
    }
  ],
  "rebaseWhen": "conflicted",
  "semanticCommits": "enabled",
  "timezone": "UTC",
  "updateNotScheduled": false
}
```

Keep enabled managers and included paths aligned with actual files. Verify regex captures on real examples; ownership identities and registry credentials belong to the destination.

[Renovate regex managers](https://docs.renovatebot.com/modules/manager/regex/)
