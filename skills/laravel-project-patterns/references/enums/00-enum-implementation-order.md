# Enums: Implementation Order

Implement string-backed enums with value helpers, literal alphabets, case mappings, translated metadata or a custom display sequence. Select only the behavior needed for the task.

Inspect the enum, consumers, translations and installed traits. Examples use `archtechx/enums` 1.1.2 and PHP 8.3+. `#[Override]` marks the parent-method override in `TranslationKey::method()`; `options()` in the ordered example replaces a trait method without that attribute. Reuse existing concerns; retrieve their implementation only when changing them.

Keep strict types, explicit backing values, typed methods and array-shape PHPDoc. Alphabetize independent imports, trait uses, case declarations and peer methods; place static methods before instance methods. Declaration order determines `values()`; preserve an inspected contract even when it is not alphabetical. Display order belongs in `orderedCases()`.

1. [Backed values and callable cases](01-backed-values.md)
2. [Literal alphabet values](02-alphabet-values.md)
3. [Alphabet and label mappings](03-alphabet-mappings.md)
4. [Translation metadata and options](04-translated-options.md)
5. [Ordered options and final-state predicates](05-ordered-status.md)

Retrieve through the skill's search/read commands. This is selection order, not a requirement to read every file. Enum tests have their own [checklist](../tests/enums/00-enum-test-order.md).
