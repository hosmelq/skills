# Retrieval benchmark

## Selection

The original 455-document corpus used nine fixed positive tasks:

| Method | Targets found |
| --- | ---: |
| BM25 | 8/9 |
| Embeddings | 7/9 |
| Hybrid | 9/9 |
| Hybrid with a generative helper | 8/9 |

The helper increased median caller context from 4,278 to 9,818 tokens. Hybrid
retrieval was retained; fixed score or top-one thresholds lost required examples.

## Context

Across three fixed tasks and 17 phases, compact instructions and context reuse
reduced median emitted skill/catalog text by 36.2%, or 34.4% including queries and
commands. Each task used a fresh agent with shared context across its phases.

The same 27 fixed queries ranked every expected reference first before and after
the prose cleanup, with complete bounded reads across the 260-reference catalog.

| Emitted text | Before | After |
| --- | ---: | ---: |
| Entrypoint, once per context | 995 | 841 |
| HTTP Resource search/read response | 590–1,142 | 599–1,153 |

## Limits

Counts use `o200k_base` on emitted text, not billing. They exclude provider wrappers,
hidden reasoning, generated PHP and project discovery. Fixed probes with predetermined
targets do not measure autonomous task accuracy. Earlier trials required follow-up
searches, so shortlist size does not establish coverage. PHP syntax and static
contract checks passed; application/database tests were not run.

Design references: [OpenAI on skills](https://developers.openai.com/blog/rethinking-skills-and-prompts-for-gpt-6-astra)
and [Laravel on Markdown indexes](https://laravel.com/blog/semantic-memory-or-just-markdown).
