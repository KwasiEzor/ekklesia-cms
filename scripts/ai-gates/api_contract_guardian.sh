#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/common.sh"

report="artifacts/api-contract.md"
violations=()

api_changed=0
if has_changes_in '^routes/api\.php$|^app/Http/Controllers/Api/|^app/Http/Requests/'; then
  api_changed=1
fi

if (( api_changed == 1 )); then
  if ! has_changes_in '^tests/Feature/Api/'; then
    violations+=("API surface changed without API feature test updates")
  fi

  if ! has_changes_in '^docs/guide/changelog\.md$|^docs/architecture/'; then
    violations+=("API surface changed without docs/changelog update")
  fi
fi

if ((${#violations[@]} > 0)); then
  write_report "$report" "FAIL" "API Contract Guardian" <<REPORT
## Findings
$(printf -- '- %s\n' "${violations[@]}")
REPORT
  exit 1
fi

write_report "$report" "PASS" "API Contract Guardian" <<'REPORT'
## Findings
- API contract guard passed for changed scope.
REPORT
