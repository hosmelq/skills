# Verify the Current PR Commit Before Signoff

Use this where `gh signoff` is the installed repository extension. Require a clean named branch, an open non-draft PR and matching HEAD; check base ancestry, run local checks, then recheck checkout/PR state before submitting signoff for the captured commit.

```toml
[tasks."signoff:check"]
depends = ["services:start"]
description = "Runs the CI checks locally."
run = [
  "vendor/bin/composer-dependency-analyser",
  "composer normalize --dry-run",
  "composer dump-autoload --optimize --strict-psr",
  "vendor/bin/pest --ci --parallel",
  "composer phpstan",
  "composer pint -- --test",
  "composer rector -- --dry-run",
]
```

```bash
#!/usr/bin/env bash
set -euo pipefail

exec 2>&1

fail() {
  printf 'signoff: %s\n' "$1" >&2
  exit "${2:-1}"
}

if [ "$#" -ne 0 ]; then
  fail "Usage: $0"
fi

repo_root="$(git rev-parse --show-toplevel)"
cd "${repo_root}"

if ! branch="$(git symbolic-ref --quiet --short HEAD)"; then
  fail "Check out a branch before running signoff"
fi

sha="$(git rev-parse HEAD)"

check_checkout() {
  local draft pr pr_sha state status

  if [ "${branch}" != "$(git symbolic-ref --quiet --short HEAD)" ] || [ "${sha}" != "$(git rev-parse HEAD)" ]; then
    fail "The checked-out branch or commit changed during signoff checks"
  fi

  status="$(git status --porcelain --untracked-files=all)"

  if [ -n "${status}" ]; then
    fail "Commit or stash uncommitted changes before running signoff:"$'\n'"${status}"
  fi

  if ! pr="$(gh pr view --json baseRefName,headRefOid,isDraft,state --jq '[.state, .isDraft, .baseRefName, .headRefOid] | @tsv')"; then
    fail "Unable to read the pull request for ${branch}"
  fi

  IFS=$'\t' read -r state draft base pr_sha <<<"${pr}"

  if [ "${state}" != "OPEN" ]; then
    fail "Only open pull requests can be signed off, and the one for ${branch} is ${state}"
  fi

  if [ "${draft}" = "true" ]; then
    fail "Mark the pull request for ${branch} as ready for review before signing off"
  fi

  if [ "${sha}" != "${pr_sha}" ]; then
    fail "HEAD (${sha}) must match the pull request's latest commit (${pr_sha})"
  fi
}

check_checkout

git fetch --quiet origin "${base}" || fail "Unable to fetch ${base}" "$?"
git merge-base --is-ancestor FETCH_HEAD HEAD || fail "Rebase ${branch} onto ${base} before signing off"

echo "Running signoff checks for ${branch} (${sha})"
mise run signoff:check || fail "The signoff checks failed for ${branch} (${sha})" "$?"

check_checkout
gh signoff --commit "${sha}" || fail "gh signoff failed for ${branch} (${sha})" "$?"

echo "Signed off on the pull request for ${branch} (${sha})"
```

The example check task covers PHP tooling; preserve every required check in the destination’s actual task. `gh signoff --commit` is an extension call, not a PR review, approval or comment.
