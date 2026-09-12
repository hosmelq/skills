---
name: durable-workflow-control
description: >-
  Preserve and resume repo-local workflow state across interruptions. Use for
  durable queues, feedback loops, or existing control artifacts that need a
  persistent next action, evidence links, and completion criteria.
---

# Durable Workflow Control

Keep enough state to resume the user's objective without reconstructing the
conversation. Use this for work that needs a persistent cursor; ordinary notes,
one-turn research, and goal-only requests do not need queue machinery.

## Working Contract

- Follow the user's current request and existing authorization. A control file
  records that scope; it does not grant additional authority. Reconcile new
  instructions and resolved blockers before applying an older stop state.
- Continue useful, authorized work until the requested outcome, an explicit
  checkpoint, or a concrete blocker is reached. Answer side questions and then
  resume unless the user pauses, cancels, or redirects the work.
- Keep the current cursor compact and link evidence separately. Read supporting
  artifacts only when the next decision needs them or their inputs changed.
- Record what was verified and the exact next action. Row-level `done` does not
  mean workflow `complete`; exhausted budgets and plausible conclusions do not
  prove completion.
- Recheck passing work only when changed inputs, a failure, an unresolved concern,
  or the user's request makes it necessary. Repeated verification and state
  maintenance alone are not progress.
- Create platform goals only when explicitly requested by the user or required
  by higher-priority instructions. A goal does not supply a scheduler or expand
  the workflow's authorization.

## Persistent State

Use `.agents/workflows/<skill-name>/<objective-slug>/control.md`. This skill's
owner name is `durable-workflow-control`; an extension owns a sibling directory.
Keep independent objectives separate and preserve existing objective paths when
resuming. Do not fall back to `.orchestration` or another state root.

`Current State` holds the active status and next action. Link `evidence.md`,
`history.md`, `sources.md`, or `work.md` only when the workflow needs them; do not
create empty support files. Preserve an extension's routing fields and ownership.

## Read As Needed

- To initialize, resume, or resolve scope and stop conditions, read
  [the lifecycle protocol](references/protocol.md#requested-gate).
- To create state, copy the [primary control template](references/templates.md#primary-control-file);
  add modules only when they affect execution or substantiate a claim.
- For research or broad review, read [source scope and coverage](references/protocol.md#source-scope-and-coverage).
- For shared writes or delegated slices, read [write discipline and concurrency](references/protocol.md#write-discipline-and-concurrency).
- For repeated attempts, read [action and observation discipline](references/protocol.md#action-and-observation-discipline).
- To improve a workflow from observed results, read [human review learning](references/protocol.md#human-review-learning).
- Before declaring the objective complete, use the [closure criteria](references/protocol.md#closure-audit).
