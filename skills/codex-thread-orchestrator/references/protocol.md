# Compact Visible-Task Protocol

## State

`<root>` is the absolute path
`<repository>/.agents/workflows/codex-thread-orchestrator`, where `<repository>`
is the selected repository root. `<p>` is the parent thread ID verified from the
runtime; `<child-id>` is a verified child thread ID. IDs must start with an ASCII
letter or digit and contain only ASCII letters, digits, `.`, `_`, or `-`; do not
rewrite an incompatible ID to fit the layout.

Use this exact layout so the recovery script can find the task:

```text
<root>/<p>/parent
<root>/<p>/control.md
<root>/<p>/active
<root>/<p>/children/<child-id>.md
```

Create the permanent `parent` marker file before dispatch. `active` holds only
the current objective. The parent's directory survives objective changes.
Use `control.md` for routing; when `$durable-workflow-control` is needed, add its
cursor fields to this same file rather than creating another control. The
routing index is:

```text
parent: <id> @ <host>
objective: o2
phase: implementation
delivery: d4
expected: <child-id> / c4
events: review_ready|research_needed|blocked|decision_required
review: not_required | pending | <review-thread-id> | rules:<AGENTS-and-skill-paths>
next: route review or dependency
children: <id> implementation active requested_model:<argument-or-omitted> requested_reasoning:<argument-or-omitted> settings_attestation:requested
accepted: c3
outbox: send:d4 -> <child-id> committed
last: o1 done - <one-line summary>
```
Keep one unindented `children:` line per child, with its ID as a separate
whitespace-delimited token; the recovery script uses these declarations to detect
missing child files. Each child owns its file. Allow only events for the current
role and phase. After approval, a finalize delivery expects only `completed` or
`blocked`. Sender identity must come from trusted runtime metadata, such as
`<codex_delegation>.source_thread_id` when exposed by the runtime, never from the
child payload. Persist review scope, acceptance, `AGENTS.md`, and skill paths
once; reference child evidence instead of copying it.

## Dispatch

Only an explicit request for visible delegation authorizes creation. The API
names below describe the supported visible-thread interface; confirm live
schemas, trusted sender metadata, and the needed create, inspect, and message
operations before relying on them. Missing capabilities block dependent steps,
not unrelated authorized work. Native subagents are not substitutes for visible
threads when that distinction is part of the request.

Use `list_projects` to identify one saved project matching the selected
repository and parent host. Resolve zero or multiple matches before creation.
Choose the environment and any model/reasoning arguments from the user's current
choices and supported runtime settings. Use documented inheritance when
appropriate; record omitted arguments as omitted, not as verified inheritance.
This protocol requires the parent and children to share the repository checkout
and the same absolute state paths. Verify that the selected environment provides
that access before creation; an isolated checkout or remote host is not sufficient
without verified shared storage. If access cannot be established, block that
dispatch rather than giving a child an inaccessible state path.

Persist the creation token and exact launch arguments as `committed` before
calling `create_thread(prompt=<payload>, ...)` with that project's verified
target. Do not create a worktree or fork merely to satisfy this protocol.

```text
role: implementation|research|review
requested_model: <exact model argument or omitted>
requested_reasoning: <exact thinking argument or omitted>
settings_attestation: requested
creation: <persisted-creation-token>
parent: <parent-thread-id> @ <parent-host-id>
state: <root>/<p>/children/<your-thread-id>.md
start: create state; persist this contract and current delivery before work
delivery: d1
callback: c1
scope: <bounded responsibility>
writes: <exact paths or none>
acceptance: <measurable outcome>
rules: <AGENTS.md and task-domain skills; exclude control/orchestrator skills>
artifact: <exact detailed-report path or none>
finish: <allowed event>; persist event/result/brief refs/outbox; send parent once {event,delivery,callback,summary,evidence:"child-state",next[,kind]}; blocked kind=recoverable|external; record sent|rejected|ambiguous; final=summary+state; create no tasks
```

The child retains those settings and resolves its ID from runtime metadata or
one `list_threads(query=<creation-token>)` snapshot with exactly one matching
task; zero or multiple matches block its dependent work. It creates its file
before starting. Record returned child and host IDs beside the launch arguments
and mark creation sent. Use supported runtime status or wait operations when
needed to coordinate; avoid repeated snapshots without a new reason. End the
turn with work pending only for a requested asynchronous mode with verified
parent reactivation. A message API alone does not prove reactivation.

Include the creation token verbatim in the initial prompt and invoke
`create_thread` at most once for that token. An error, timeout, or missing or
malformed receipt is `ambiguous`, never proof that no task was created. Persist
that state. Reconcile with one read-only `list_threads(query=<creation-token>)`
snapshot and `read_thread` its matches; adopt exactly one. If unresolved, repeat
reconciliation only on a later activation or changed evidence. Zero matches
remain ambiguous and multiple matches block that assignment. Never retry
creation or mint a replacement token for the same assignment.

Persisted settings prove only what was requested. Change `settings_attestation`
to `runtime-verified` only when a runtime receipt or inspection explicitly
reports model and reasoning, and retain that evidence. Otherwise keep
`requested` and do not claim an effective configuration.

Reuse the child for follow-ups within its assignment, preserving its settings.
Send only the changed contract fields:

```text
delivery: d2 supersedes d1
callback: c2
change: <new instruction>
```

The child abandons `d1`, persists `d2`, and continues. A superseded callback is
stale and causes no acceptance, transition, or redispatch.

## Callback

Persist first, then send once:

```json
{"event":"review_ready","delivery":"d2","callback":"c2",
 "summary":"Implementation and checks ready for review",
 "evidence":"child-state","next":"review"}
```

Use `send_message_to_thread(threadId=<parent-id>, hostId=<parent-host>,
prompt=<json>)` without overrides. Known events: `review_ready`, `review_approved`, `changes_requested`, `completed`, `research_needed`, `research_completed`, `blocked`, `decision_required`, and requested `compact_recovered`.

Before mutation, require every shown string field, then validate trusted sender,
current delivery, expected callback, and the transition's allowed events.
`blocked` additionally requires `kind` equal to `recoverable` or `external`; use
`decision_required` for choices. Accept each callback once. Evidence stays in
the child file/artifacts. Local final is only a summary plus state path.

Persist every outbox row as `committed`; after the single attempt mark it
`sent`, `rejected:<error>`, or `ambiguous`. On error, reconcile once with
`read_thread`; never retry. After reconciliation, the parent may record
`accepted_from_state` only when a runtime-verified read of the expected task's
final points to its exact state file and that file matches the expected
delivery, callback, allowed event, and a rejected or ambiguous outbox. Otherwise
block that transition; never invent a wrapper callback or ask the child to resend.

## Routing And Review

When independent review is required, use:

```text
implementation -> review_ready -> visible reviewer
                     changes -> same implementation -> same reviewer
                    approved -> implementation finalizes -> completed
```

Decide whether review is required from the request and unresolved risk. A
`review_ready` delivery routes to an independent, read-only, project-local
reviewer. Accept routine research, inventories, and deterministic evidence
without adding a reviewer when the evidence meets acceptance. When review is
not required, allow completion after the agreed verification.

Reference durable state. The reviewer sends one minimal callback. Return changes
to and finalize through the same tasks; when review is required, accept
`completed` only after approval.

Route `research_needed` only for bounded questions. Stop when evidence answers
the decision; exhaustive audits require explicit scope; research never implements.

## Visible Task Ownership

The parent creates and reuses visible children; children return bounded
`research_needed` requests instead of creating more tasks. The parent may
investigate directly or route a researcher. Assign non-overlapping ownership,
applicable instructions, and only the domain skills needed for the work. The
parent may inspect code and run verification while respecting those assignments.

## Closure And Recovery

After acceptance and planned reuse end, archive the child when the runtime
supports it: persist the outbox entry, call
`set_thread_archived(archived=true, threadId=<child-id>, hostId=<host-id>)`, and
record its receipt. Report unavailable cleanup separately from the work result.
At objective closure, remove only `active`, set the cursor idle, and retain one
summary. Keep the `parent` marker and child state needed for recovery.

[hooks/user-hooks.json](../hooks/user-hooks.json) is an optional registration
example for [the recovery script](../scripts/compact_rehydration.py), not proof
of an installed hook. Its command assumes this skill is installed under
`$HOME/.agents/skills/codex-thread-orchestrator`; verify the actual script path
and the runtime's supported hook registration before using it.

The script expects JSON with `hook_event_name:"SessionStart"`, `source:"compact"`,
`session_id`, and `cwd`. It returns `hookSpecificOutput.additionalContext` for a
compatible runtime to inject. Verify that the runtime emits this payload and
consumes that output before relying on automatic recovery; local tests only
establish the script's behavior.

From `cwd` and every ancestor, the script searches the exact root layout above.
It selects one mapping for the session: parent -> `<root>/<p>/control.md`; child
-> `<root>/<p>/children/<child-id>.md`. Missing declared state or multiple
mappings, including across roots, produce a blocker for work dependent on that
state. Unrelated sessions stay silent. Reread the mapped file before resuming;
without a verified hook, recover from the recorded state path explicitly.
