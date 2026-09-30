# Configure PHP Static Analysis

Use PHPStan with the installed Laravel, strictness and disallowed-call extensions, `tomasvotruba/type-coverage` (including `type_perfect`) and `ticketswap/phpstan-error-formatter`. Keep identifier/path exceptions narrow and inspect actual app paths. `tips.treatPhpDocTypesAsCertain` controls a tip, not the global analysis setting.

```neon
includes:
  - vendor/spaze/phpstan-disallowed-calls/disallowed-dangerous-calls.neon
  - vendor/spaze/phpstan-disallowed-calls/disallowed-execution-calls.neon
  - vendor/spaze/phpstan-disallowed-calls/disallowed-insecure-calls.neon
  - vendor/spaze/phpstan-disallowed-calls/disallowed-loose-calls.neon

parameters:
  checkAuthCallsWhenInRequestScope: true
  checkBenevolentUnionTypes: true
  checkConfigTypes: true
  checkModelProperties: false
  checkOctaneCompatibility: true
  editorUrl: 'phpstorm://open?file=%%file%%&line=%%line%%'
  errorFormat: ticketswap
  excludePaths:
    analyse:
      - bootstrap/cache
  ignoreErrors:
    -
      identifier: method.childParameterType
      path: app/Actions/Fortify/CreateNewUser.php
    -
      identifier: return.deprecatedInterface
      path: app/Actions/Fortify/PasswordValidationRules.php
  level: max
  noEnvCallsOutsideOfConfig: true
  noUnnecessaryEnumerableToArrayCalls: true
  paths:
    - app
    - bootstrap
    - config
    - database
    - routes
  strictRules:
    dynamicCallOnStaticMethod: false
  type_coverage:
    constant: 100
    declare: 100
    param: 100
    property: 100
    return: 100
  type_perfect:
    no_mixed: true
    null_over_false: true
    narrow_param: true
    narrow_return: true
  tips:
    treatPhpDocTypesAsCertain: false
  tmpDir: .cache/phpstan
```
