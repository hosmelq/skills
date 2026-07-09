---
name: codex-thread-orchestrator
description: >-
  Route work from a user-started parent Codex task to visible child tasks using
  compact durable state, proactive callbacks, independent review, reuse, and
  compaction recovery.
---

# Codex Thread Orchestrator

Activate `$durable-workflow-control` first; it owns generic goals, evidence,
budgets, blockers, verification, and closure. This extension adds only
visible-task identity, routing, reuse, compact messages, and recovery.

Read [references/protocol.md](references/protocol.md) before creating a child.
Discover applicable `AGENTS.md`, skills, and operations from the live runtime;
never hardcode projects, frameworks, or unavailable tools.

## Restrict The Parent

The parent only reads coordination state, writes its orchestrator root, operates
visible Codex tasks, validates callbacks, routes transitions and independent
review, performs the one bounded `wait_threads` required after
`create_thread`, and answers status from durable state.

It never reads project code, investigates, implements, runs project commands or
tests, edits deliverables, launches Agents V2 helpers, or polls. Delegate all
technical work to visible children.

## Keep State And Messages Small

```text
.agents/workflows/codex-thread-orchestrator/<parent-id>/
├── parent
├── control.md
├── active
└── children/<child-id>.md
```

`parent` and the minimal DWC-compatible `control.md` persist. `active` contains
only the current objective and is absent while idle. The parent owns root files;
each child exclusively owns its compact file. Persist the review recipe once;
reference it and child evidence instead of copying either.

Send stable assignment facts once. Follow-ups contain only delivery, callback,
supersession, and the change. Reject stale or duplicate callbacks.

## Route Visible Tasks

Create project-local visible tasks only on explicit request. Never use a
worktree, `fork_thread`, or handshake, and omit model/reasoning overrides unless
explicitly requested. Follow the exact project-selection rules in the protocol.

After creation, make one bounded `wait_threads` call for required progress, emit
the created-task directive, and end; never wait for completion. The child
persists its outcome, sends one minimal callback to the trusted parent, and
leaves a short final pointing to its state file.

The implementation child sends `review_ready`; the parent creates or reuses one
independent visible review task from its durable recipe. Findings return to the
same implementation child and the same reviewer checks corrections until
approval. The child then finalizes and sends `completed`. Reuse related research;
archive a task only after reuse ends.

## Keep Helpers Child-Owned

Visible children may launch bounded Scouts (Sol low/read-only), Workers (Sol
medium), and Smart workers (Sol high). Use `fork_turns: "none"`, non-overlapping
ownership, and leaf/no-delegation rules. They return only to the child; forward
a summary/reference, never full finals. Children never launch reviewers.

For technical validation, the child activates `$crabbox` when repository config
exists and its CLI is usable, unless the user opts out. The parent only routes
this requirement.

## Recover After Compaction

The installed `SessionStart(compact)` hook immediately injects the exact parent
control or child state path before resumed reasoning/tools. Current Codex does
not run start hooks in internal/system helpers. Parent recovery uses the
permanent `parent` marker, never `active`.
