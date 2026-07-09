# Compact Visible-Task Protocol

## State

For verified parent `<p>` use:

```text
<root>/<p>/parent
<root>/<p>/control.md
<root>/<p>/active
<root>/<p>/children/<child-id>.md
```

`parent` is permanent; `active` holds only the current objective. `control.md`
is the sole parent-owned cursor: keep the minimal `$durable-workflow-control`
gate, status, next action, budget, blockers, artifact links, verifier, and
closure fields with this routing index:

```text
parent: <id> @ <host>
objective: o2
phase: implementation
delivery: d4
expected: <child-id> / c4
events: review_ready|research_needed|blocked|decision_required
review: pending | <review-thread-id> | rules:<AGENTS-and-skill-paths>
next: route review or dependency
children: <id> implementation active
accepted: c3
outbox: send:d4 -> <child-id> committed
last: o1 done - <one-line summary>
```
Allow only events for the current role and phase, never their union. After
approval, a new implementation-finalize delivery expects only `completed` or `blocked`.
Each child owns its file; the trusted wrapper identifies the sender. Never copy
child evidence, logs, scope, settings, or callbacks. Persist review scope,
acceptance, applicable `AGENTS.md`, and skill paths once. The child records new
paths in its state; the parent adds only references before review.
## Dispatch

Only an explicit user request for visible delegation authorizes creation.
Persist the creation token as `committed`, call `list_projects`, and require one
saved project matching working directory plus parent host ID; block on zero or
multiple matches. Call `create_thread` with `environment: local`. Never use a
worktree, `fork_thread`, polling, handshake, or unrequested model/reasoning overrides.

```text
role: implementation|research|review
creation: <persisted-creation-token>
parent: <parent-thread-id> @ <parent-host-id>
state: <root>/<p>/children/<your-thread-id>.md
delivery: d1
callback: c1
scope: <bounded responsibility>
writes: <exact paths or none>
acceptance: <measurable outcome>
rules: <verified AGENTS.md and selected skill paths>
```

The child retains the creation token, discovers its ID, creates its file, and
starts. Record returned child and host IDs, mark creation sent, make one bounded `wait_threads` call, emit
`::created-thread{threadId="<id>"}`, and end without waiting for completion.
Child state stores stable facts once plus current phase, delivery, callback,
supersession, result, evidence, blocker, and next action.
Follow-ups contain only:

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

Before any create, message, follow-up, callback, or archive, persist its unique
outbox row as `committed`; mark `sent` only after receipt. After crash or unknown
send, reconcile via `list_threads`/`read_thread`. Never blindly resend; if one
result cannot be proven, record an external blocker.

## Routing And Review

```text
implementation -> review_ready -> visible reviewer
                     changes -> same implementation -> same reviewer
                    approved -> implementation finalizes -> completed
```

On `review_ready`, create or reuse one independent, read-only, project-local
visible reviewer with no delegation. Reference its durable recipe and the
implementation state, never chat-derived scope. It sends one minimal
`review_approved`, `changes_requested`, or `blocked` callback and ends. Route
changes to the same implementation and its next `review_ready` to the same
reviewer. Accept `completed` only after approval.

Route `research_needed` to reusable visible research only for a bounded question
that cannot safely finish inside the child turn. Research never implements.

## Internal Helpers

Only a child may use leaf Scouts (Sol low/read-only), Workers (Sol medium/exact
writes), and Smart workers (Sol high/hard bounded work). Use
`fork_turns: "none"`, include needed `AGENTS.md` rules and skill paths, and
prohibit overlap, delegation, orchestrator-state access, and top-parent
messages. Children never launch reviewers; review is the parent-owned visible
task above. The parent never launches Agents V2 helpers.

Resolve helpers in the child turn. Persist identity, ownership, status, and
evidence reference; never full finals. Use `$crabbox` when configured and usable
unless the user opts out; the child owns its runs.

## Closure And Recovery

Archive only after accepted result and planned reuse end. At objective closure,
remove only `active`, set the cursor idle, and retain one summary.

`SessionStart(compact)` immediately injects one path before resumed work:
parent -> `<p>/control.md`; every visible child, including review -> its child
file. Thus the parent recovers the review recipe and reviewer identity.
Missing/duplicate mappings inject a blocker. Internal/system helpers do not run
start hooks. Reread first.
