#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/common.sh"

report="artifacts/policy-validation.md"
policy_file="scripts/ai-gates/policy.json"

json_output="$(php scripts/ai-gates/analyzers/validate_policy.php --policy "$policy_file")"
valid="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo ($r["valid"] ?? false) ? "true" : "false";' <<< "$json_output")"
errors_md="$(php -r '
$r=json_decode(stream_get_contents(STDIN),true);
$errors=$r["errors"] ?? [];
if ($errors === []) { echo "- None\n"; exit(0); }
foreach ($errors as $e) { echo "- {$e}\n"; }
' <<< "$json_output")"
warnings_md="$(php -r '
$r=json_decode(stream_get_contents(STDIN),true);
$warnings=$r["warnings"] ?? [];
if ($warnings === []) { echo "- None\n"; exit(0); }
foreach ($warnings as $w) { echo "- {$w}\n"; }
' <<< "$json_output")"

if [[ "$valid" != "true" ]]; then
  write_report "$report" "FAIL" "AI Gate Policy Validation" <<REPORT
## Errors
${errors_md}

## Warnings
${warnings_md}
REPORT
  while IFS= read -r err; do
    [[ -z "$err" ]] && continue
    [[ "$err" == '- None' ]] && continue
    echo "::error::Policy validation ${err#- }"
  done <<< "$errors_md"
  exit 1
fi

write_report "$report" "PASS" "AI Gate Policy Validation" <<REPORT
## Errors
${errors_md}

## Warnings
${warnings_md}
REPORT

while IFS= read -r warn; do
  [[ -z "$warn" ]] && continue
  [[ "$warn" == '- None' ]] && continue
  echo "::warning::Policy validation ${warn#- }"
done <<< "$warnings_md"
