# Run Staged PHP Checks in Order

Use a Lefthook piped group to run Rector, then Pint, then PHPStan on staged PHP. Restage fixes, exclude Blade and adapt the analysis test-directory exclusion to the inspected project.

```yaml
glob_matcher: doublestar

pre-commit:
  jobs:
    - name: Backend
      group:
        piped: true
        jobs:
          - name: Rector (fix)
            glob: '**/*.php'
            exclude:
              - '**/*.blade.php'
            run: composer rector -- {staged_files}
            stage_fixed: true

          - name: Pint (fix)
            glob: '**/*.php'
            exclude:
              - '**/*.blade.php'
            run: composer pint -- {staged_files}
            stage_fixed: true

          - name: PHPStan
            glob: '**/*.php'
            exclude:
              - '**/*.blade.php'
              - 'tests/**'
            run: composer phpstan -- {staged_files}
```

[Lefthook jobs](https://lefthook.dev/configuration/jobs/)
