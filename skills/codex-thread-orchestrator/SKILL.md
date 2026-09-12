---
name: codex-thread-orchestrator
description: >-
  Coordinate explicitly requested visible Codex threads with durable identity,
  callbacks, and recovery when compatible thread APIs are available. Ordinary
  native subagent coordination does not use this skill.
---

# Codex Thread Orchestrator

Use this adapter for visible threads the user has requested. Confirm the live
runtime supports the required operations and trusted thread identity before
dispatch. A missing capability blocks its dependent step; continue other
authorized work without claiming that native subagents are visible threads.

## Coordination

- The parent owns routing and root state; each child owns its compact state file
  and assigned work. The parent may inspect, investigate, and verify results.
  Keep writes and delegated responsibilities non-overlapping.
- Record acceptance, applicable instructions, and evidence references once.
  Follow-ups identify the new delivery, callback, superseded delivery, and change.
- Preserve trusted sender validation, allowed transitions, and persist-before-send
  delivery. Reconcile ambiguous creation results without creating duplicates.
- Select models and reasoning from the current session's choices and supported
  runtime settings. Record requested settings separately from verified settings.
- Continue coordinating after dispatch. End the turn with work pending only when
  the requested asynchronous mode has a verified reactivation mechanism.
- Use independent review when requested or warranted by unresolved risk. Reuse
  the implementer and reviewer while their assignment remains relevant.

## State And Recovery

Keep the [protocol layout](references/protocol.md#state) for visible-task
identity and recovery. Use `$durable-workflow-control` when the objective also
needs queue, evidence, budget, or resume management; extend this same control
file rather than creating a parallel cursor.

The bundled hook is an optional integration example. Its presence does not
install it or prove that the runtime emits compatible compaction events. Verify
the installation, script path, and runtime contract before relying on it.
Recovery uses the permanent `parent` marker, never `active`.

## Read As Needed

- Before creating or reusing a child, read [dispatch](references/protocol.md#dispatch).
- To send or accept a result, read [callback validation](references/protocol.md#callback).
- When review is needed, read [routing and review](references/protocol.md#routing-and-review).
- For completion, compaction, or hook setup, read [closure and recovery](references/protocol.md#closure-and-recovery).
