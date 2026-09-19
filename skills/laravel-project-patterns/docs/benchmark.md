# Retrieval benchmark

2026-09-12 · catalog snapshot `612b7e0` · 455 searchable Markdown documents.

**Decision:** BM25 + Qwen3-Embedding 0.6B Q8_0, fused with RRF, without a
generative helper. Keep complete references within a 4,000-token read budget.

Eight variants ran on the same 12 synthetic tasks: nine positive cases and
three initially labeled negatives. Primary results had blinded semantic review.

| Variant | Positive cases with complete evidence |
| --- | --- |
| BM25 | 8/9 |
| Embeddings | 7/9 |
| Hybrid | 9/9 |
| Hybrid + Qwen3.5 4B helper | 8/9 |

Hybrid used a median 4,278 caller-context tokens and returned seven documents;
the helper increased combined context to 9,818 tokens. At a 2,000-token read
budget, hybrid covered 8/9 cases; 8,000 did not improve on 4,000.

The original HTTP runtime measured about 0.45 seconds for warm-model search
after clearing query caches, using six CPU threads; indexing took 428 seconds.
Its separate probes peaked at 2.63 GiB for a query and 3.53 GiB for indexing.
These timings and memory figures do not describe the Python binding.

These results evaluate retrieval, not application changes. Two negative labels
were ambiguous, rankings varied with cache state, and the sample is small.
The integration now loads the model directly inside Python and frees it after
each command. Its replay preserved all 36 requirements across the nine positive
cases; median complete-command latency was 1.49 seconds on the same machine.
Retrieved rules must still respect the live project's contracts.

## Create reference layout

2026-09-19 · snapshot `9c0f3d4` · 14 focused tasks and two unsupported queries.
An alternative shortened prose and split eight references into twelve while
preserving complete test examples. Markdown shrank from 8,279 to 7,679 tokens,
but mean full search-response size grew from 2,889 to 3,076 tokens at the same
3,200-token source budget; returned references grew from 2.29 to 4.14 per query.
Both layouts supplied the needed behavior for all 14 focused tasks, including
an adequate alternative enum example, and returned candidates for both unsupported
queries. Compacting alone did not reduce delivered context: selection still fills
the budget. Measure relevance and repeated content across queries before claiming
a context saving; shorter files alone are insufficient evidence.
