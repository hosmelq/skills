#!/usr/bin/env python3
"""Inject a compacted visible task's exact orchestrator state path."""
import json, re, sys
from pathlib import Path

SAFE_ID = re.compile(r"^[A-Za-z0-9][A-Za-z0-9._-]*$")
FIELDS = ("event", "delivery", "callback", "summary", "evidence", "next")
EVENTS = {"review_ready", "review_approved", "changes_requested", "completed",
          "research_needed", "research_completed", "blocked",
          "decision_required", "compact_recovered"}


def accept_and_reserve(index, sender, message, effect=None):
    callback, event = message.get("callback"), message.get("event")
    if callback in index.get("accepted", []):
        return "duplicate"
    if message.get("delivery") != index.get("delivery"):
        return "stale"
    bad_message = (
        any(not isinstance(message.get(field), str) or not message[field] for field in FIELDS)
        or sender != index.get("expected_sender")
        or callback != index.get("expected_callback")
        or event not in EVENTS
        or event not in index.get("expected_events", [])
        or (event == "blocked" and message.get("kind") not in {"recoverable", "external"})
    )
    outbox = index.get("outbox", {})
    bad_effect = effect is not None and (
        not isinstance(effect, dict)
        or not all(isinstance(effect.get(key), str) and effect[key] for key in ("id", "target"))
        or effect.get("id") in outbox
    )
    if bad_message or bad_effect:
        return "invalid"
    update = {"accepted": [*index.get("accepted", []), callback]}
    if effect:
        update["outbox"] = {**outbox, effect["id"]: {
            "target": effect["target"], "status": "committed"}}
    index.update(update)
    return "accepted"


def effect_recovery(index, effect_id):
    status = index.get("outbox", {}).get(effect_id, {}).get("status")
    return {"sent": "done", "committed": "reconcile"}.get(status, "block")


def callback_route(message):
    event, kind = message.get("event"), message.get("kind")
    if event == "blocked":
        return {"recoverable": "redispatch", "external": "stop_external"}.get(kind, "invalid")
    return {"review_ready": "review", "review_approved": "finalize",
            "changes_requested": "correct", "completed": "close",
            "research_needed": "research", "research_completed": "resume",
            "decision_required": "ask_user",
            "compact_recovered": "recover"}.get(event, "invalid")


def close_objective(index):
    index.update({"objective": None, "phase": "idle", "active": False})


def find_root(cwd):
    for directory in (cwd, *cwd.parents):
        root = directory / ".agents/workflows/codex-thread-orchestrator"
        if root.is_dir():
            return root


def mappings(root, thread_id):
    if not SAFE_ID.fullmatch(thread_id):
        return []
    mapped = []
    parent = root / thread_id
    if (parent / "parent").is_file():
        mapped.append(parent / "control.md")
    for child in root.glob(f"*/children/{thread_id}.md"):
        if (child.parents[1] / "parent").is_file():
            mapped.append(child)
    if not mapped:
        for control in root.glob("*/control.md"):
            try:
                declared = thread_id in control.read_text().split()
            except OSError:
                declared = False
            if declared and (control.parent / "parent").is_file():
                mapped.append(control.parent / "children" / f"{thread_id}.md")
    return sorted(set(mapped))


def recovery_context(root, thread_id):
    mapped = mappings(root, thread_id)
    if len(mapped) == 1 and mapped[0].is_file():
        return (f"Compaction recovery: re-read {mapped[0]} before reasoning or tools; "
                "it is the durable source of truth, not chat history.")
    if mapped:
        reason = "multiple mappings" if len(mapped) > 1 else "mapped state is missing"
        return f"Compaction recovery blocker for {thread_id}: {reason}. Stop and report it."


def main():
    try:
        payload = json.load(sys.stdin)
        if (payload.get("hook_event_name"), payload.get("source")) != ("SessionStart", "compact"):
            return 0
        thread_id, cwd = payload.get("session_id"), payload.get("cwd")
        if not isinstance(thread_id, str) or not isinstance(cwd, str):
            return 0
        root = find_root(Path(cwd).resolve())
        context = recovery_context(root, thread_id) if root else None
        if context:
            json.dump({"hookSpecificOutput": {"hookEventName": "SessionStart",
                      "additionalContext": context}}, sys.stdout, separators=(",", ":"))
            sys.stdout.write("\n")
    except (AttributeError, OSError, TypeError, ValueError):
        pass
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
