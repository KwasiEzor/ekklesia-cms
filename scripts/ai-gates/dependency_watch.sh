#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/common.sh"

report="artifacts/dependency-watch.md"

dep_changed=0
if has_changes_in '^composer\.lock$|^package-lock\.json$'; then
  dep_changed=1
fi

if (( dep_changed == 0 )); then
  write_report "$report" "PASS" "Dependency Supply Chain Watch" <<'REPORT'
## Findings
- No lockfile changes detected in this diff.
REPORT
  exit 0
fi

write_report "$report" "PASS" "Dependency Supply Chain Watch" <<'REPORT'
## Findings
- Lockfile changes detected.
- Run deeper advisory scanning (Dependabot/GitHub Advisory/OSV) in hosted security tooling.

## Changed Lockfiles
- composer.lock and/or package-lock.json
REPORT
