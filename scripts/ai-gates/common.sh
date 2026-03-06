#!/usr/bin/env bash
set -euo pipefail

search_q() {
  local pattern="$1"
  if command -v rg >/dev/null 2>&1; then
    rg -q "$pattern"
  else
    grep -Eq "$pattern"
  fi
}

search_lines() {
  local pattern="$1"
  if command -v rg >/dev/null 2>&1; then
    rg "$pattern"
  else
    grep -E "$pattern"
  fi
}

search_files_with_line_numbers() {
  local pattern="$1"
  shift
  if command -v rg >/dev/null 2>&1; then
    rg -n "$pattern" "$@"
  else
    grep -En "$pattern" "$@"
  fi
}

get_diff_range() {
  if [[ -n "${AI_DIFF_RANGE:-}" ]]; then
    printf '%s' "$AI_DIFF_RANGE"
    return
  fi

  if [[ -n "${GITHUB_BASE_REF:-}" ]]; then
    printf 'origin/%s...HEAD' "$GITHUB_BASE_REF"
    return
  fi

  printf 'HEAD~1...HEAD'
}

changed_files() {
  git diff --name-only "$(get_diff_range)"
}

has_changes_in() {
  local pattern="$1"
  if changed_files | search_q "$pattern"; then
    return 0
  fi
  return 1
}

count_changes_in() {
  local pattern="$1"
  (changed_files | search_lines "$pattern" || true) | wc -l | tr -d ' '
}

ensure_artifact_dir() {
  mkdir -p artifacts
}

write_report() {
  local file="$1"
  local status="$2"
  local title="$3"
  shift 3

  ensure_artifact_dir
  {
    echo "# ${title}"
    echo
    echo "- Status: ${status}"
    echo "- Diff range: $(get_diff_range)"
    echo "- Generated at (UTC): $(date -u '+%Y-%m-%d %H:%M:%S')"
    echo
    cat
  } > "$file"
}

list_matches() {
  local pattern="$1"
  local files
  files="$(changed_files | search_lines '\.php$' || true)"

  if [[ -z "$files" ]]; then
    return 0
  fi

  search_files_with_line_numbers "$pattern" $files || true
}
