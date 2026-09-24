# Enum Tests: Ordered Contract Checklist

Enum unit tests cover backed values, alphabet and label mappings, boolean predicates, translated options and explicit case order. Model casts, resource serialization and architecture rules belong to their own categories.

Inspect the enum, its traits and test bootstrap. Use the applicable cases below in this order, with standalone `it()` when the suite has no `describe`. Keep every inspected value and dataset row, including false results; expected arrays must be literal, not derived from the method under test. Separate setup, operation and assertions with blank lines.

1. [Available Values](01-values.md) — `defines available values`
2. [Alphabet Mapping](02-alphabets.md) — `defines alphabets`
3. [Label Mapping](03-labels.md) — `defines labels`
4. [Final State Predicate](04-finality.md) — `determines whether a status is final`
5. [Translated Options](05-options.md) — `returns options`
6. [Explicit Case Order](06-case-order.md) — `defines ordered cases`
