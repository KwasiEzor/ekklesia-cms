#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/common.sh"

report="artifacts/ux-review.md"
violations=()

ui_changed=0
if has_changes_in '^app/Filament/|^resources/views/|^resources/css/|^resources/js/'; then
  ui_changed=1
fi

if (( ui_changed == 1 )); then
  if ! has_changes_in '^tests/Feature/.*(Filament|UI|Admin)|^tests/Feature/'; then
    violations+=("UI-related files changed without Feature test updates")
  fi

  if ! has_changes_in '^lang/(fr|en)/'; then
    violations+=("UI-related files changed without translation review in lang/fr or lang/en")
  fi
fi

if ((${#violations[@]} > 0)); then
  write_report "$report" "FAIL" "Filament UX Consistency Critic" <<REPORT
## Findings
$(printf -- '- %s\n' "${violations[@]}")
REPORT
  exit 1
fi

write_report "$report" "PASS" "Filament UX Consistency Critic" <<'REPORT'
## Findings
- UX consistency gate passed for changed scope.
- If no UI files changed, this check is a no-op pass.
REPORT
