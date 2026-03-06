#!/usr/bin/env bash
set -euo pipefail

artifacts_dir="${1:-artifacts}"
run_url="${GITHUB_SERVER_URL:-https://github.com}/${GITHUB_REPOSITORY:-}/actions/runs/${GITHUB_RUN_ID:-}"
code_scanning_url="${GITHUB_SERVER_URL:-https://github.com}/${GITHUB_REPOSITORY:-}/security/code-scanning?query=tool%3Aekklesia-ai-gates"

status_of() {
  local file="$1"
  if [[ ! -f "$file" ]]; then
    echo "MISSING"
    return
  fi
  awk -F': ' '/^- Status: /{print $2; exit}' "$file"
}

icon_for() {
  local st="$1"
  case "$st" in
    PASS) echo "✅" ;;
    WARN) echo "⚠️" ;;
    FAIL) echo "❌" ;;
    MISSING) echo "➖" ;;
    *) echo "➖" ;;
  esac
}

row() {
  local label="$1"
  local file="$2"
  local st
  st="$(status_of "$file")"
  printf '| %s | %s %s | `%s` |\n' "$label" "$(icon_for "$st")" "$st" "$(basename "$file")"
}

{
  echo '<!-- ai-gates-summary -->'
  echo '## AI Gates Summary'
  echo
  echo '| Gate | Status | Artifact |'
  echo '|---|---|---|'
  row 'Policy Validation' "$artifacts_dir/policy-validation.md"
  row 'Tenant Isolation Audit' "$artifacts_dir/tenant-audit.md"
  row 'Security Review' "$artifacts_dir/security-review.md"
  row 'Prompt Safety' "$artifacts_dir/prompt-safety.md"
  row 'API Contract Guardian' "$artifacts_dir/api-contract.md"
  row 'PR Risk and Tests' "$artifacts_dir/pr-risk.md"
  row 'Test Gaps (Diff Coverage)' "$artifacts_dir/test-gaps.md"
  row 'UX Consistency' "$artifacts_dir/ux-review.md"
  row 'Docs Sync' "$artifacts_dir/docs-sync.md"
  echo
  echo "Workflow run artifacts: ${run_url}"
  echo "Code scanning (AI gates): ${code_scanning_url}"
} > "$artifacts_dir/pr-summary.md"
