# Configure Test Suites and Their Environment

Use PHPUnit configuration to discover actual suite paths, coverage sources and runtime settings. Keep Architecture as a file suite and Feature/Integration/Unit as separate directories when the project follows this layout.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
        bootstrap="vendor/autoload.php"
        cacheDirectory=".cache/phpunit"
        colors="true"
        failOnPhpunitWarning="true"
>
    <testsuites>
        <testsuite name="Architecture">
            <file>tests/ArchitectureTest.php</file>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <ini name="memory_limit" value="512M"/>

        <env name="APP_ENV" value="testing"/>
    </php>
</phpunit>
```

The env file supplies database connectivity; keep its key generated per environment. Configure application test bindings and transaction traits separately.
