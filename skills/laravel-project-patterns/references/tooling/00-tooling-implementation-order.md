# Backend Tooling Order

Read only the matching script or configuration. Adapt paths and tool versions to the inspected project; preserve command dependencies and execution order.

1. [Configure Backend Toolchain and Setup Tasks](01-toolchain-setup.md).
2. [Connect Composer Commands and Lifecycle Hooks](02-composer-commands.md).
3. [Start the Local Database and Record Its Port](03-local-database.md).
4. [Run Staged PHP Checks in Order](04-staged-checks.md).
5. [Configure PHP Static Analysis](05-static-analysis.md).
6. [Configure PHP Formatting and Member Order](06-code-style.md).
7. [Configure Automated PHP Refactoring](07-code-upgrades.md).
8. [Analyze Composer Dependencies and Runtime Integrations](08-dependency-analysis.md).
9. [Configure Test Suites and Their Environment](09-test-configuration.md).
10. [Verify the Current PR Commit Before Signoff](10-signoff.md).
11. [Share PHP Dependencies and Run Test Shards](11-ci-checks.md).
12. [Record and Share a Test Impact Baseline](12-ci-test-baseline.md).
13. [Publish Generated Files Against an Expected Commit](13-publish-generated-files.md).
14. [Analyze PR Workflows as Untrusted Data](14-ci-security.md).
15. [Run a Manually Dispatched CI Agent](15-ci-agent.md).
16. [Update Backend Tools and Dependency Manifests](16-dependency-updates.md).
