# Retrieval benchmark

## Selective reads · 2026-09-19

**Decision:** keep BM25 + Qwen3-Embedding 0.6B Q8_0 in Python. Search returns
short applicability descriptions; the caller selects complete examples to read.
Per-context receipts suppress unchanged repeats. No server or generative helper.

Compared the nine-reference baseline at `1369269`, a compact nine-file layout,
and twelve behavior-focused references. All 22 test bodies and 14 canonical
names survive; the source audit still covers 55 declarations / 59 variants.

| Measured text context | Before | Final layout |
| --- | ---: | ---: |
| 14 focused tasks: mean complete search/read responses | 3,030.5 | 1,225.6 |
| Independent dependent-select task: skill + all catalog responses/diagnostics | 6,901 | 3,544 |

The focused replay used expected-behavior selection and preserved 14/14 coverage;
it does not measure an autonomous agent's choices. Six additional supported
contracts recovered all 15 expected behaviors, one through a focused follow-up.
Their mean complete responses cost 2,601.8 tokens versus 3,043.8 for the
intermediate nine-description index; the follow-up is included.

The independent task produced all four required cases using two references.
A second fresh agent covered a
composite contract with seven cases, two three-variant datasets and six distinct
references; it used 6,351 input-text tokens and correctly skipped a browser-only
request. An independent reviewer found no supplied-contract omissions. PHP syntax
passed; these synthetic tasks have no application suite to execute.

An entire nine-entry description index cost more on focused tasks than five
candidates (1,719 versus 1,419 response tokens before the final split). Broad
requests need more descriptions or focused follow-ups. Rank/score thresholds and
fixed top-one selection lost required examples or admitted unrelated queries.
Neither a nonempty shortlist nor its size proves coverage. The CLI does not
classify unsupported tasks; the skill and consuming agent must enforce scope.

Counts use `o200k_base`, not billing: full responses and repeated reads count;
provider envelopes, hidden reasoning and application discovery are excluded.
The benchmark has 14 earlier focused contracts, six new supported contracts,
four unsupported queries and two prior task replays. It does not establish
semantic accuracy for a future 300-reference catalog. Recheck whole-task coverage
and context as new reference families are added.

## Earlier model/layout comparison

The original `612b7e0` corpus had 455 searchable documents. On nine positive
synthetic tasks, BM25 covered 8/9, embeddings 7/9, hybrid 9/9 and hybrid plus a
Qwen3.5 4B helper 8/9. Median caller context grew from 4,278 with hybrid to 9,818
with the helper. The subsequent Python replay retained all 36 required behaviors;
its median complete command took 1.49 seconds on the test machine.

Compacting/splitting the earlier eight-file create layout alone reduced Markdown
by 7.2% but increased delivered response context by 6.5%: greedy packing filled
the freed space. Selection and cumulative measurement were necessary.

Design references: [OpenAI on skills and prompts](https://developers.openai.com/blog/rethinking-skills-and-prompts-for-gpt-6-astra)
and [Laravel on an index over Markdown](https://laravel.com/blog/semantic-memory-or-just-markdown).
Published advice informed the alternatives; the local trials determined this change.
