# Durable Workflow Control Templates

Copy only the required fields and modules. Operational rules live in
[the protocol](protocol.md); these templates describe persisted data. Paths below
use this skill's owner name; extensions substitute their own skill name.

## Primary Control File

```markdown
# <Workflow Name> - Control

## Purpose
- Outcome: <concrete outcome and completion criteria>
- Scope: <authorized work>
- Exclusions: <actual restrictions, if any>

## Requested Gate
- Gate: discuss | artifact_only | initialize_and_wait | initialize_and_start | resume | reset | audit | improve
- Gate evidence: <current request and applicable prior authorization>
- Stop at: <completion or explicit checkpoint>

## Artifact Links
- Primary control: .agents/workflows/durable-workflow-control/<objective-slug>/control.md
- Supporting artifacts: <existing links and roles, if needed>

## Current State
- Current tick: Q0.
- Workflow status: pending | in_progress | complete | blocked: <plain reason>.
- Stop-state precheck: continue | stop: <current reason after reconciliation>.
- Next action: <exact action>.

## Blockers
- <Actual blocker and required input, or none>
```

## Optional Primary Sections

Add fields when they determine the next action or help a consumer recover.
Preserve existing extension fields instead of replacing them with this template.

```markdown
## Budget And Permissions
- Limits: <user/platform budget or bounded retry policy; omit absent limits>
- Allowed writes: <authorized surface when useful to distinguish ownership>
- Required authorization/checkpoint: <unresolved requirement, or none>
- Stop-on-budget: <record progress and remaining work; never assume completion>

## Queue
| Tick | Slice | Claim owner | Expected revision | Verifier | Status | Evidence | Next action |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Q1 | <bounded work> | <owner> | <revision> | <check> | pending | <link> | <action> |

## Closure Checklist
- [ ] Requested outcome fulfilled.
- [ ] Required verification and coverage evidence recorded.
- [ ] Remaining decisions resolved or scope explicitly revised.
- [ ] Final verdict and relevant limitations recorded.
```

Optional `Current State` fields: `Progress locator`, `Last verified
revision/state`, `Claim owner`, and `Final verdict`. Existing controls may retain
more detailed budgets and artifact links when those fields remain relevant.

## Support Artifact Header

```markdown
# <Workflow Name> - <Evidence | History | Sources | Work>
- Primary control: .agents/workflows/durable-workflow-control/<objective-slug>/control.md
- Artifact role: evidence | history | sources | work
- Current summary: <information relevant to the next decision>
```

Support artifacts carry detail; `control.md` remains the cursor.

## Standard Work Matrix

Use for multiple independently tracked slices. Row IDs remain stable; ownership
and expected revisions matter when work is shared.

```markdown
| Id | Slice/source | Acceptance/verifier | Claim owner | Expected revision | Status | Evidence | Verification | Blocker/decision | Next action |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| W-001 | <task> | <criterion/check> | <owner> | <revision> | pending | <link> | <result> | <none> | <action> |
```

## Research Source Ledger

```markdown
### S-<NNN> - <Source title>
- Source/path: <link, id, or path>
- Access status: read | attempted_inaccessible | withheld | superseded | not_needed
- Read method/date: <method and date when freshness matters>
- Parent source / necessity: <why followed, if relevant>
- Key evidence and limits: <source-specific notes>
- Notes location: <link if notes are separate>
```

## Source Notes

Use separate notes only when they would obscure the ledger.

```markdown
### <Source title>
- Source: <ledger id/link>
- Evidence: <what the source supports>
- Implication: <effect on the current work>
- Limits/exclusions: <what cannot be concluded>
```

## Coverage Contract

```markdown
- Coverage claim to prove: <specific claim>
- Universe: <source set, area, diff, or dataset>
- Method: targeted_search | grouped_review | sampled_review | full_read | deterministic_test | external_verifier | human_review | mixed
- Reproducible evidence: <commands, queries, source ids, or reviewed groups>
- Known limits/blind spots: <named limits>
- Coverage checker: <appropriate reconciliation/check>
```

## Evidence Row

```markdown
### E-<NNN> - <Finding>
- Claim: <finding>
- Evidence source(s): <ids, paths, or verification output>
- Verification and limits: <what was checked and remains uncertain>
- Contradictions: <conflicting evidence, if any>
- Implication: <change, decision, or no-op>
```

## Alternatives

```markdown
| Option | Evidence | Tradeoff | Decision | Reason |
| --- | --- | --- | --- | --- |
| <option> | <links> | <cost/risk/benefit> | selected/rejected/deferred | <why> |
```

## Final Synthesis

```markdown
- Claim/outcome: <result>
- Evidence map: <source/evidence/checker ids>
- Coverage limit: <named limit, or none>
- Decision/handoff: <delivered output and remaining work, if any>
```

## Action/Observation Row

For exploratory or structured attempts, as described in
[action and observation discipline](protocol.md#action-and-observation-discipline).

```markdown
### AO-<NNN> - <Attempt>
- Tick: Q<N>
- Proposed action: <bounded action>
- Validity precheck: <pass/fail and reason, when useful>
- Checker and limits: <verification and what it cannot prove>
- Observation: invalid | illegal | failed | succeeded | stop_requested | stop_accepted | stop_rejected | iteration_limit_reached
- Feedback/result: <specific change or failure>
- Best-so-far / accepted output: <verified artifact>
- Strategy update / next action: <what the feedback changes>
- Evidence location: <link>
```

## Tick Entry

Keep in history only when the current cursor and evidence do not retain needed
run detail.

```markdown
### Tick Q<N> - <Name>
- Run identity: <thread/run/progress locator>
- Status: pending | in_progress | done | blocked: <plain reason>
- Slice/output: <bounded unit and artifact>
- Evidence/checker: <links and relevant limits>
- Next action: <action or concrete stop reason>
```

## Guardrail Or Approval Row

Use for an actual permission boundary or interrupted side effect, not as a
mandatory approval stage for every write.

```markdown
### G-<NNN> - <Condition>
- Condition: <missing authorization or side effect to reconcile>
- Authorization source: <current instruction or actual unresolved requirement>
- Tool/call id and receipt: <id/result, when relevant>
- Decision: pending | approved | rejected | not_applicable
- Resume state: <progress locator and next action>
- Idempotency note: <what may already have happened>
```

## Human Review Evidence Row

```markdown
### HR-<NNN> - <Reviewed output>
- Run/version: <input/output version and run identity>
- Evidence: <source trace, verification, and feedback/diff>
- Human outcome: accepted_unchanged | edited_accepted | rejected_deleted | pending | conflicting
- Signal: <demonstrated defect, preference, weak signal, or no signal>
- Lesson/change candidate: <bounded proposal or none>
- Resolution: <applied, deferred, rejected, or no-op with reason>
```

## Outer Improvement Loop

```markdown
- Trigger and scope: <requested improvement or authorized recurring review>
- Run window: <reviewed run ids/versions>
- Evidence: <feedback, verified defects, or recurring patterns>
- Proposed output: <smallest supported change or no-op>
- Invariants to preserve: <consumer contracts and actual permission boundaries>
- Verification: <check tied to the behavior being improved>
```

## Subagent Validation Row

```markdown
### SA-<NNN> - <Agent/slice>
- Prompt scope: <responsibility, write surface, acceptance>
- Files/sources reviewed: <actual coverage>
- Findings and evidence: <links>
- Resolution: <accepted/rejected findings and reason>
```

## Goal Text

Only for an explicitly requested goal or one required by higher-priority
instructions; a durable workflow does not require a platform goal.

```text
Objective: <concrete outcome>.
Scope: <authorized work and exclusions>.
Queue/control: <verified link, if present>.
Supporting artifacts: <necessary verified links, if present>.
Completion criteria: <observable result and required verification>.
Continuation: <authorized work, actual checkpoints, and budget if specified>.
```

## Completion Audit

Use a table when there are several requirements; a short evidence-backed closure
entry is enough for a small workflow.

```markdown
| Requirement | Evidence inspected | Status | Notes |
| --- | --- | --- | --- |
| <requirement> | <actual artifact/check> | proven/contradicted/incomplete/weak/missing | <limits> |

## Coverage Verdict
- Claim and method: <claim and actual inspection method>
- Checker result: proven | partial | contradicted | missing

## Final Verdict
- Verdict: ready | ready_with_constraints | ready_except_named_decisions | partial_discovery_ready_for_next_pass | blocked_by_specific_gap | blocked_by_missing_evidence
- Constraints/remaining work: <named limitations or none>
```
