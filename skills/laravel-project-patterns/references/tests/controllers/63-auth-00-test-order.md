# Authentication Tests: Ordered Cases

Ordered Pest authentication cases for email verification codes, signed Apple tokens, Google client verification and the browser verification prompt. Select the actual endpoint strategy and preserve its complete datasets and response contracts.

Keep these cases at file scope when the suite tests an invocable controller without `describe`. Apply only the inspected endpoint contracts; use one `validates fields` dataset per endpoint.

## Case Order

1. `validates fields`
2. `rejects an expired verification code`
3. `rejects an already used verification code`
4. `rejects an unverifiable identity token`
5. `rejects an identity token for another audience`
6. `rejects an expired identity token`
7. `rejects an identity token from another issuer`
8. `rejects an identity token with a mismatched nonce`
9. `rejects a new identity without an email claim`
10. `rejects a registered email without a linked identity`
11. `rejects an email linked to another identity`
12. `authenticates an existing identity`
13. `authenticates an existing identity without an email claim`
14. `keeps the account email when the identity email changes`
15. `creates and authenticates a new account`
16. `authenticates and verifies an existing account`
17. `requests a verification code`
18. `shows the verification page`

## Contracts

Use the same canonical names for equivalent behavior across providers. Apple verifies signed JWTs against fake keys; Google mocks its verification client; email login consumes a stored one-time code. Keep those strategies separate. Missing email on a linked identity differs from missing email on a new identity. An unlinked registered email and an email linked to another subject remain separate fixtures.

Preserve exact 422 fields, account versus provider email, profile origin, verification/consumption timestamps, token response and token count where asserted. Code generation is mocked in the request endpoint; notification routing and code-model identity are its contract.

Examples are synthetic. Adapt models, helpers, public IDs, namespaces and suite paths to the project. Retrieve only the needed provider examples; load the signed-token helper only when no equivalent fixture exists.
