#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/common.sh"

report="artifacts/prompt-safety.md"
violations=()

if changed_files | rg -q '^(app|config|routes)/'; then
  injection_matches="$(list_matches 'systemPrompt\s*=.*\$request|prompt\s*=.*\$request|messages\s*=.*\$request')"
  if [[ -n "$injection_matches" ]]; then
    violations+=("Prompt/message assignment appears to directly interpolate request payload")
  fi

  if changed_files | rg -q '^app/Http/Controllers/Api/Assistant' && ! changed_files | rg -q '^tests/Feature/Api/'; then
    violations+=("Assistant controller changed without API feature tests")
  fi
fi

if ((${#violations[@]} > 0)); then
  write_report "$report" "FAIL" "Prompt Safety Guard" <<REPORT
## Findings
$(printf -- '- %s\n' "${violations[@]}")

## Matched Lines
\`\`\`
${injection_matches:-}
\`\`\`
REPORT
  exit 1
fi

write_report "$report" "PASS" "Prompt Safety Guard" <<'REPORT'
## Findings
- No direct prompt-injection regression pattern detected in changed files.
REPORT
