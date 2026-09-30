# Publish Generated Files Against an Expected Commit

Use this helper after generation in a trusted Linux CI checkout. Pass only owned generated paths and the captured branch/SHA. Build payloads from staged blobs and require the expected remote head so another writer’s commit is preserved.

`scripts/publish-generated-files`

```bash
#!/usr/bin/env bash
set -euo pipefail

: "${GH_TOKEN:?Required}"
: "${GITHUB_REPOSITORY:?Required}"
: "${HEAD_REF:?Required}"
: "${HEAD_SHA:?Required}"
: "${RUNNER_TEMP:?Required}"

if [ "$#" -eq 0 ]; then
  echo "Usage: $0 <owned-generated-path>..." >&2
  exit 1
fi

commit_files() {
  local headline="$1"
  local current_head_sha next_head_sha path status
  shift

  if git diff --cached --quiet -- "$@"; then
    return
  fi

  : > "${RUNNER_TEMP}/additions.json"
  : > "${RUNNER_TEMP}/deletions.json"

  while IFS= read -r -d '' status && IFS= read -r -d '' path; do
    if [ "${status}" = 'D' ]; then
      jq --null-input --arg path "${path}" '{path: $path}' >> "${RUNNER_TEMP}/deletions.json"

      continue
    fi

    git show ":${path}" | base64 --wrap 0 > "${RUNNER_TEMP}/contents.base64"

    jq --null-input \
      --arg path "${path}" \
      --rawfile contents "${RUNNER_TEMP}/contents.base64" \
      '{contents: $contents, path: $path}' >> "${RUNNER_TEMP}/additions.json"
  done < <(git diff --cached --name-status --no-renames -z -- "$@")

  jq --null-input \
    --arg head_ref "${HEAD_REF}" \
    --arg head_sha "${HEAD_SHA}" \
    --arg headline "${headline}" \
    --arg repository "${GITHUB_REPOSITORY}" \
    --slurpfile additions "${RUNNER_TEMP}/additions.json" \
    --slurpfile deletions "${RUNNER_TEMP}/deletions.json" \
    '{
      query: "mutation($input: CreateCommitOnBranchInput!) { createCommitOnBranch(input: $input) { commit { oid } } }",
      variables: {
        input: {
          branch: {branchName: $head_ref, repositoryNameWithOwner: $repository},
          expectedHeadOid: $head_sha,
          fileChanges: {additions: $additions, deletions: $deletions},
          message: {headline: $headline}
        }
      }
    }' > "${RUNNER_TEMP}/generated-files-commit.json"

  if next_head_sha="$(gh api graphql --input "${RUNNER_TEMP}/generated-files-commit.json" --jq '.data.createCommitOnBranch.commit.oid')"; then
    HEAD_SHA="${next_head_sha}"
    return
  fi

  current_head_sha="$(gh api "repos/${GITHUB_REPOSITORY}/git/ref/heads/${HEAD_REF}" --jq '.object.sha')"

  if [ "${current_head_sha}" != "${HEAD_SHA}" ]; then
    echo 'The branch changed during generation; review the new head before retrying.'
    exit 0
  fi

  exit 1
}

git add --all -- "$@"
commit_files 'chore: update generated files' "$@"
```

Workflow gate; append the destination’s generation and publication steps:

```yaml
on:
  pull_request_review:
    types: [submitted]

concurrency:
  cancel-in-progress: true
  group: ${{ github.workflow }}-${{ github.event.pull_request.number }}

permissions: {}

jobs:
  regenerate:
    environment: generated-files
    if: >-
      github.event.review.state == 'approved' &&
      github.event.pull_request.user.id == fromJSON(vars.DEPENDENCY_BOT_ID || '0') &&
      github.event.pull_request.head.repo.full_name == github.repository
    permissions:
      contents: write
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@3d3c42e5aac5ba805825da76410c181273ba90b1
        with:
          persist-credentials: false
          ref: ${{ github.event.pull_request.head.sha }}
```

Set `GH_TOKEN`, `GITHUB_REPOSITORY`, `HEAD_REF`, `HEAD_SHA` and `RUNNER_TEMP` for the helper. The bot ID identifies the PR author; `approved` does not identify a trusted reviewer. Configure required environment reviewers or repository rules, and supply the generation steps’ registry credentials through that environment. For separate path groups, call `commit_files` sequentially in the same shell so each success advances `HEAD_SHA`. A moved head exits without retrying; earlier groups may already be published.

[GitHub commit mutation](https://docs.github.com/en/graphql/reference/commits#createcommitonbranch)
