# Reference Skeleton

## Purpose

Define the canonical markdown structure for every reference document under `references/**`.

## When To Use

Use this contract when creating or updating any skill reference markdown file.

## Required Pattern

Use two document roles.

Router references select the applicable branch and own cross-cutting rules. Use
these H2 sections in this exact order:

1. `## Purpose`
2. `## When To Use`
3. `## Required Pattern`
4. `## Coverage Expectations`
5. `## Do Not`
6. `## Related References`

Focused leaves, including navigation maps under `references/maps/**`, inherit
the router's cross-cutting rules and use only:

1. `## When To Use`
2. `## Pattern`
3. `## Related References`

Every router link must say what decision or scenario makes the leaf applicable.
Keep the route from `SKILL.md` to a domain router and then to its leaf short.
Do not repeat generic coverage or prohibition text in every leaf.

Preserve all technical pattern coverage, datasets, and snippets. When an example uses a real module/entity name, convert it to a complete synthetic example instead of deleting it.

Ground changes to technical recommendations in current code or authoritative documentation for the relevant contract. Compare siblings by precondition, operation, ownership boundary, and outcome; directory proximity alone is insufficient. State when a convention applies only to a particular architecture or library. Routing and editorial changes can use the catalog and references as evidence without inspecting an original project. If technical evidence is unavailable, preserve the existing example's semantics and record the limitation instead of inventing a broader rule.

When a reference touches model integration coverage, link to `references/tests/Integration/Models/README.md` instead of duplicating its full policy text.

## Coverage Expectations

This file defines documentation structure expectations only. Router references
define coverage expectations for the project code they map to; focused leaves
preserve the selected pattern or example.

For controller references, preserve action-order conventions and nested binding coverage where the destination project owns those contracts. Describe their applicability so examples do not impose route structure or test style on other projects.

## Do Not

- Do not drop technical coverage during normalization; convert real module/entity examples to synthetic placeholders.
- Do not weaken the model integration policy above.
- Do not make agents read sibling leaves before they can determine which branch applies.

## Related References

- [`SKILL.md`](../SKILL.md)
- [`references/MAP.md`](MAP.md)
- [`references/tests/Integration/Models/README.md`](tests/Integration/Models/README.md)
- [`references/tests/Unit/Models/README.md`](tests/Unit/Models/README.md)
