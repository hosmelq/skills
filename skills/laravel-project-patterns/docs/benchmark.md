# Retrieval benchmark

2026-09-19. Keep SQLite FTS5 BM25 + Qwen3-Embedding 0.6B Q8_0 in Python.
Search selects descriptions; explicit reads return complete examples. No server
or generative helper. Markdown remains the source of truth.

## Cumulative Tasks

Compared frozen `1f29f5d` with compact instructions, incremental descriptions,
literal Markdown output and reuse before searching. Three synthetic tasks and
17 phases were fixed before implementation; each version/task used a fresh
agent, with one shared context across that task's phases.

| Complete task | Before | After | Reduction |
| --- | ---: | ---: | ---: |
| Dependent selects across three controllers, six phases | 4,565 | 2,579 | 43.5% |
| Nested DDD routes across two controllers, five phases | 5,125 | 3,616 | 29.4% |
| Option eligibility across three controllers, six phases | 10,066 | 6,424 | 36.2% |

Counts include the skill and every catalog stdout/stderr emission across all
phases, including repeats. Median reduction: **36.2%**; including query JSON and
CLI arguments: **34.4%**; adding the fixed supplied contracts: **30.6%**.
Neither version repeated full sources. Replaying identical queries/selections
isolated output-format/metadata savings of 14.8%, 15.1% and 31.9%, respectively;
that replay measures serialization, not autonomous retrieval choices.

All 22 reference test bodies, 14 canonical names, ordered rules and adaptations
are unchanged; the prior audit's 55 declarations / 59 variants remain covered.
Independent review found no supplied-contract omissions in any phase of either
version. The generated PHP passes syntax checks. These tasks have no application runtime;
unspecified enum casts, factory defaults and relationship names remain assumptions.
One packet does not specify its test namespace, so preserving that declaration
cannot be scored. The browser-only phase correctly causes no catalog calls.

These are `o200k_base` text counts, **not billing**. They exclude provider
wrappers, hidden reasoning, generated PHP and live-project discovery. Existing
context is counted when emitted, not again on every model turn. One paired run
per task and twelve references do not establish statistical reliability or
accuracy for a future 300-reference catalog. Repeat whole-task trials as it grows.

## Destroy Expansion

The destroy expansion brought the catalog to 22 references. An exhaustive controller audit mapped 125
destroy declarations to 34 distinct cases, represented by 48 complete examples
and explicit adaptations in ten new references. Independent review found no
missing cases or actionable defects in the unchanged create references.

Three fixed create probes retained their required candidates. Five destroy
probes found the needed families; one needed a focused follow-up for the ordered
checklist. Shortlists can still mix actions: callers must select by applicability.

One fresh agent completed a synthetic create/reactivate task across three phases:
two searches, two reads and five references; the final phase reused context.
The frozen trial emitted **5,821 tokens**, including the skill; query JSON and CLI
arguments bring that to **6,487**. Counts use the exclusions above. Review found
the supplied contracts preserved, and both PHP files pass syntax checks; no
application runtime was available. These focused checks do not establish
large-catalog accuracy.

## Edit Expansion

2026-09-20: 34 references. The edit audit mapped 88 declarations / 90 variants to
29 behavior families, represented by 46 complete examples in twelve files.
Independent review confirmed coverage. Six focused edit probes found the needed
sources; one of six unchanged create/destroy probes needed a focused follow-up.

A frozen synthetic trial covered two controllers across three phases: three
searches, three reads, six references; the last two phases reused context.
Skill plus catalog responses totaled **7,531 tokens**, or **8,909** with queries
and CLI arguments (**9,775** including supplied contracts). The generated tests
preserved those contracts and passed syntax checks; no application runtime was
available. These counts use the exclusions above and do not establish large-catalog
accuracy. An editorial checklist reorder after the trial changed no PHP examples.

## Remaining Controller Blocks

2026-09-21. Independent source audits and reference reviews preserve complete
examples and datasets. Each frozen consumer trial uses one fresh agent across
three phases, then syntax checks and contract review; no application runtime.

| Block | Audited declarations / variants | Catalog files | Skill + responses | With queries + commands |
| --- | ---: | ---: | ---: | ---: |
| Index | 68 / 68 | 47 | 3,756 | 4,946 |
| Show | 92 / 94 | 67 | 5,627 | 7,637 |
| Store | 172 / 281 | 122 | 6,085 | 7,586 |
| Update | 186 / 295 | 186 | 6,626 | 8,608 |

Index adds 39 complete examples in 13 files. Seven focused probes found the
needed families in five candidates. Four existing probes retained their targets;
one pre-existing create miss still needed a focused follow-up. The consumer used
three searches, three reads and four references; its final phase reused context.
Counts and limitations above apply; these trials do not establish large-catalog accuracy.

Show preserves 54 examples in 20 files. Splitting authentication and binding by
route depth reduced the same supplied task from 7,097 to 5,627 tokens; including
queries and commands, 8,437 to 7,637. Both fresh consumers preserved the contracts.
Six focused probes found the needed families, two with follow-up searches;
three unchanged create/destroy/edit probes retained their targets. One run per
version measures this task only, including each consumer's retrieval choices.

Store preserves 145 examples and 13 complete datasets in 55 files. Its consumer
preserved nested validation, optional/null mapping and API contracts. Eight
focused probes found their needed families, one with a follow-up; three existing
create/destroy/edit probes retained their targets. The final phase reused context.

Update preserves 168 examples and 15 complete datasets in 64 files. Its consumer
preserved geographic normalization and decision dispatch/error contracts, then
reused context; counts include one rejected query and its correction. Ten focused
probes found their needed families: one broad ancestry probe required two focused
queries and a retry with ten candidates. Six prior-action probes retained their
targets. These results confirm those queries, not guaranteed top-five coverage.

## Earlier Selection Evidence

The original `612b7e0` corpus contained 455 documents. On nine positive tasks,
BM25 covered 8/9, embeddings 7/9, hybrid 9/9 and hybrid plus a Qwen3.5 4B helper
8/9. The helper increased median caller context from 4,278 to 9,818 tokens.
An earlier fourteen-task replay reduced complete search/read responses from
3,030.5 to 1,225.6 tokens with expected-behavior selection. Fixed top-one and
score thresholds lost required examples or admitted unrelated queries.

Design references: [OpenAI on skills](https://developers.openai.com/blog/rethinking-skills-and-prompts-for-gpt-6-astra)
and [Laravel on Markdown indexes](https://laravel.com/blog/semantic-memory-or-just-markdown).
Local trials, rather than those articles alone, determined this implementation.
