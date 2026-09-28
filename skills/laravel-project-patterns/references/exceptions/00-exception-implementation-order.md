# Exceptions: Implementation Order

Choose the needed exception shape:

1. [Named reasons](01-named-reasons.md): one or several reason factories.
2. [Fixed messages](02-fixed-messages.md): zero-argument constructors; extensible or final.
3. [Attempt count](03-attempt-count.md): a parameterized failure message.
4. [Field errors](04-field-errors.md): readonly field metadata and separate create/update types.
5. [Previous cause](05-previous-cause.md): optional typed database exception chaining.

Keep constructor first, then named factories alphabetically. Adapt names and messages to the caller's contracts; preserve its namespace and typed catches. These classes do not define HTTP rendering or reporting.

APIs: [PHP exceptions](https://www.php.net/manual/en/language.exceptions.extending.php), [Laravel error handling](https://laravel.com/docs/13.x/errors).
