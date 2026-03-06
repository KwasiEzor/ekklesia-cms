#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/common.sh"

report="artifacts/pr-risk.md"
score=0
reasons=()

php_changes="$(count_changes_in '^app/.*\.php$')"
route_changes="$(count_changes_in '^routes/.*\.php$')"
config_changes="$(count_changes_in '^config/.*\.php$')"
job_changes="$(count_changes_in '^app/.*/Jobs/.*\.php$|^app/Jobs/.*\.php$')"

(( score += php_changes / 8 ))
(( score += route_changes * 2 ))
(( score += config_changes * 2 ))
(( score += job_changes * 2 ))

if (( php_changes > 0 )); then reasons+=("PHP files changed: ${php_changes}"); fi
if (( route_changes > 0 )); then reasons+=("Route files changed: ${route_changes}"); fi
if (( config_changes > 0 )); then reasons+=("Config files changed: ${config_changes}"); fi
if (( job_changes > 0 )); then reasons+=("Job files changed: ${job_changes}"); fi

has_tests=0
if has_changes_in '^tests/'; then
  has_tests=1
fi

if (( score >= 8 && has_tests == 0 )); then
  write_report "$report" "FAIL" "PR Risk and Regression Review" <<REPORT
## Risk Score
- Score: ${score}
- Threshold: 8

## Drivers
$(printf -- '- %s\n' "${reasons[@]}")

## Failure Reason
- High-risk PR without test changes in \`tests/\`.
REPORT
  exit 1
fi

write_report "$report" "PASS" "PR Risk and Regression Review" <<REPORT
## Risk Score
- Score: ${score}
- Threshold: 8

## Drivers
$(printf -- '- %s\n' "${reasons[@]:-No major risk drivers}" 2>/dev/null || echo '- No major risk drivers')

## Result
- Risk gate passed.
REPORT
