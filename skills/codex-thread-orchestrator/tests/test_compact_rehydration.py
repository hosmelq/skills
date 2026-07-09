import importlib.util, json, subprocess, tempfile, unittest
from pathlib import Path

SKILL = Path(__file__).resolve().parents[1]
SCRIPT = SKILL / "scripts/compact_rehydration.py"
PROTOCOL = (SKILL / "references/protocol.md").read_text()
SKILL_TEXT = (SKILL / "SKILL.md").read_text()
SPEC = importlib.util.spec_from_file_location("router", SCRIPT)
ROUTER = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(ROUTER)


class RecoveryTest(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.cwd = Path(self.temp.name)
        self.root = self.cwd / ".agents/workflows/codex-thread-orchestrator"

    def tearDown(self): self.temp.cleanup()

    def workflow(self, parent="parent", active=True, marked=True):
        path = self.root / parent
        path.mkdir(parents=True)
        (path / "control.md").write_text("parent index\n")
        if marked: (path / "parent").touch()
        if active: (path / "active").write_text("o1\n")
        return path

    def child(self, flow, child="child"):
        path = flow / "children" / f"{child}.md"
        path.parent.mkdir()
        path.write_text("child state\n")
        return path

    def invoke(self, thread, event="SessionStart", source="compact", cwd=None):
        payload = {"session_id": thread, "cwd": str(cwd or self.cwd),
                   "hook_event_name": event, "source": source}
        return subprocess.run(["python3", str(SCRIPT)], input=json.dumps(payload),
                              text=True, capture_output=True, check=False).stdout

    def context(self, output):
        return json.loads(output)["hookSpecificOutput"]["additionalContext"]

    def test_parent_child_idle_and_nested_recover_immediately(self):
        active = self.workflow()
        child = self.child(active)
        idle = self.workflow("idle", active=False) / "control.md"
        nested = self.cwd / "a/b"; nested.mkdir(parents=True)
        for thread, path, cwd in (("parent", active / "control.md", nested),
                                  ("child", child, None), ("idle", idle, None)):
            self.assertIn(str(path), self.context(self.invoke(thread, cwd=cwd)))

    def test_unrelated_bad_and_internal_helper_events_are_silent(self):
        self.workflow(marked=False)
        for thread in ("parent", "unknown", "../parent"):
            self.assertEqual("", self.invoke(thread))
        self.assertEqual("", self.invoke("parent", source="resume"))
        self.assertEqual("", self.invoke("parent", event="SubagentStart"))

    def test_duplicate_and_missing_mapping_block(self):
        self.child(self.workflow("one"), "shared")
        self.child(self.workflow("two"), "shared")
        self.assertIn("multiple mappings", self.context(self.invoke("shared")))
        flow = self.workflow("loss")
        state = self.child(flow, "lost")
        (flow / "control.md").write_text("children: lost implementation active\n")
        state.unlink()
        self.assertIn("mapped state is missing", self.context(self.invoke("lost")))

    def test_hook_and_compact_contract(self):
        hooks = json.loads((SKILL / "hooks/user-hooks.json").read_text())["hooks"]
        self.assertEqual(["SessionStart"], list(hooks))
        self.assertEqual("compact", hooks["SessionStart"][0]["matcher"])
        flat = " ".join(PROTOCOL.split())
        for phrase in ('"evidence":"child-state"', "parent: <parent-thread-id>",
                       "creation: <persisted-creation-token>",
                       "delivery: d2 supersedes d1", "send_message_to_thread(",
                       "call `list_projects`", "working directory plus parent host ID",
                       "block on zero or multiple matches", "unrequested model/reasoning overrides",
                       "one bounded `wait_threads`", "outbox row as `committed`",
                       "minimal `$durable-workflow-control` gate",
                       "Use `$crabbox` when configured and usable",
                       "review_ready", "parent-owned visible"):
            self.assertIn(phrase, flat)
        self.assertIn("It never reads project code", " ".join(SKILL_TEXT.split()))


class RouterTest(unittest.TestCase):
    def setUp(self):
        self.index = {
            "parent": "parent", "objective": "o1", "active": True,
            "phase": "implementation", "delivery": "d2",
            "expected_sender": "child", "expected_callback": "c2",
            "expected_events": ["review_ready", "research_needed", "blocked", "decision_required"],
            "accepted": [], "outbox": {},
        }
        self.message = {"event": "review_ready", "delivery": "d2", "callback": "c2",
                        "summary": "ready", "evidence": "child-state", "next": "review"}

    def test_accept_duplicate_stale_and_crash_recovery(self):
        effect = {"id": "archive:child", "target": "child"}
        self.assertEqual("accepted", ROUTER.accept_and_reserve(
            self.index, "child", self.message, effect))
        restored = json.loads(json.dumps(self.index))
        self.assertEqual("reconcile", ROUTER.effect_recovery(restored, "archive:child"))
        self.assertEqual("duplicate", ROUTER.accept_and_reserve(
            restored, "child", self.message, effect))
        stale = dict(self.message, delivery="d1", callback="c1")
        self.assertEqual("stale", ROUTER.accept_and_reserve(restored, "child", stale))
        self.assertEqual((1, 1), (len(restored["accepted"]), len(restored["outbox"])))

    def test_invalid_messages_and_effects_do_not_mutate(self):
        cases = [
            ("other", self.message, None),
            ("child", dict(self.message, event="research_completed"), None),
            ("child", dict(self.message, event="review_approved"), None), ("child", dict(self.message, event="completed"), None),
            ("child", dict(self.message, event="blocked"), None),
            ("child", self.message, {"id": "bad"}),
        ]
        for sender, message, effect in cases:
            before = json.loads(json.dumps(self.index))
            self.assertEqual("invalid", ROUTER.accept_and_reserve(
                self.index, sender, message, effect))
            self.assertEqual(before, self.index)
        self.index["outbox"]["send"] = {"target": "x", "status": "committed"}
        before = json.loads(json.dumps(self.index))
        self.assertEqual("invalid", ROUTER.accept_and_reserve(
            self.index, "child", self.message, {"id": "send", "target": "y"}))
        self.assertEqual(before, self.index)

    def test_routes_and_parent_persists(self):
        self.assertEqual("review", ROUTER.callback_route({"event": "review_ready"}))
        self.assertEqual("finalize", ROUTER.callback_route({"event": "review_approved"}))
        self.assertEqual("correct", ROUTER.callback_route({"event": "changes_requested"}))
        self.assertEqual("close", ROUTER.callback_route({"event": "completed"}))
        self.assertEqual("redispatch", ROUTER.callback_route(
            {"event": "blocked", "kind": "recoverable"}))
        self.assertEqual("stop_external", ROUTER.callback_route(
            {"event": "blocked", "kind": "external"}))
        self.assertEqual("invalid", ROUTER.callback_route({"event": "blocked"}))
        ROUTER.close_objective(self.index)
        self.assertEqual(("parent", None, False),
                         (self.index["parent"], self.index["objective"], self.index["active"]))


if __name__ == "__main__":
    unittest.main()
