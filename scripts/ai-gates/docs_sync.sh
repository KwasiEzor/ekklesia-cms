#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/common.sh"

report="artifacts/docs-sync.md"
violations=()

behavior_changed=0
if has_changes_in '^app/|^routes/|^config/'; then
  behavior_changed=1
fi

if (( behavior_changed == 1 )); then
  if ! has_changes_in '^docs/guide/changelog\.md$|^docs/architecture/'; then
    violations+=("Behavior/config changes detected without docs update")
  fi
fi

if ((${#violations[@]} > 0)); then
  write_report "$report" "FAIL" "Docs and Changelog Sync" <<REPORT
## Findings
$(printf -- '- %s\n' "${violations[@]}")
REPORT
  exit 1
fi

write_report "$report" "PASS" "Docs and Changelog Sync" <<'REPORT'
## Findings
- Docs sync gate passed for changed scope.
REPORT
