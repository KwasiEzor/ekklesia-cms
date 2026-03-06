#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/common.sh"

report="artifacts/tenant-audit.md"
sarif_report="artifacts/tenant-audit.sarif"
policy_file="scripts/ai-gates/policy.json"
baseline_file="scripts/ai-gates/baseline.json"

scripts/ai-gates/validate_policy.sh >/dev/null

php_files="$(changed_files | search_lines '\.php$' || true)"
if [[ -z "${php_files}" ]]; then
  echo '{"active_findings":[]}' | php scripts/ai-gates/analyzers/findings_to_sarif.php --gate tenant_isolation > "$sarif_report"
  write_report "$report" "PASS" "Tenant Isolation Audit" <<'REPORT'
## Findings
- No changed PHP files in this diff.
REPORT
  exit 0
fi

json_output="$(printf '%s\n' "$php_files" | php scripts/ai-gates/analyzers/tenant_isolation_audit.php)"
filtered_output="$(php scripts/ai-gates/analyzers/filter_findings.php --gate tenant_isolation --policy "$policy_file" --baseline "$baseline_file" <<< "$json_output")"
php scripts/ai-gates/analyzers/findings_to_sarif.php --gate tenant_isolation <<< "$filtered_output" > "$sarif_report"
finding_count="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo (int)($r["counts"]["active"] ?? 0);' <<< "$filtered_output")"
suppressed_count="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo (int)($r["counts"]["suppressed"] ?? 0);' <<< "$filtered_output")"
suppressed_policy_count="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo (int)($r["counts"]["suppressed_policy"] ?? 0);' <<< "$filtered_output")"
suppressed_baseline_count="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo (int)($r["counts"]["suppressed_baseline"] ?? 0);' <<< "$filtered_output")"
fail_on="$(php -r '$p=json_decode(file_get_contents($argv[1]),true); $v=$p["thresholds"]["tenant_isolation"]["fail_on"] ?? ["high","critical"]; echo json_encode($v);' "$policy_file")"

if [[ "$finding_count" -gt 0 ]]; then
  findings_md="$(php -r '
  $r=json_decode(stream_get_contents(STDIN), true);
  foreach (($r["active_findings"] ?? []) as $f) {
    echo "- [{$f["severity"]}] {$f["code"]} {$f["file"]}:{$f["line"]} {$f["message"]}\n";
  }' <<< "$filtered_output")"

  annotations="$(php -r '
  $r=json_decode(stream_get_contents(STDIN), true);
  foreach (($r["active_findings"] ?? []) as $f) {
    $level = (($f["severity"] ?? "medium") === "high") ? "error" : "warning";
    $file = $f["file"] ?? "";
    $line = (int)($f["line"] ?? 1);
    $msg = ($f["code"] ?? "FINDING") . " " . ($f["message"] ?? "");
    echo "::{$level} file={$file},line={$line}::{$msg}\n";
  }' <<< "$filtered_output")"
  if [[ -n "$annotations" ]]; then
    printf '%s\n' "$annotations"
  fi

  should_fail="$(php -r '
  $fail=json_decode($argv[1], true) ?: ["high","critical"];
  $r=json_decode(stream_get_contents(STDIN), true);
  $active=$r["active_findings"] ?? [];
  $set=array_fill_keys(array_map("strtolower", $fail), true);
  $hit=false;
  foreach ($active as $f) {
    $sev=strtolower((string)($f["severity"] ?? "medium"));
    if (isset($set[$sev])) { $hit=true; break; }
  }
  echo $hit ? "true" : "false";
  ' "$fail_on" <<< "$filtered_output")"

  status="WARN"
  exit_code=0
  if [[ "$should_fail" == "true" ]]; then
    status="FAIL"
    exit_code=1
  fi

  write_report "$report" "$status" "Tenant Isolation Audit" <<REPORT
## Findings
${findings_md}

## Policy
- Total suppressed (policy + baseline): ${suppressed_count}
- Suppressed by policy rules: ${suppressed_policy_count}
- Suppressed by baseline snapshot: ${suppressed_baseline_count}
REPORT
  exit "$exit_code"
fi

write_report "$report" "PASS" "Tenant Isolation Audit" <<REPORT
## Findings
- AST analysis found no high-confidence tenant isolation bypass patterns in changed PHP files.

## Policy
- Total suppressed (policy + baseline): ${suppressed_count}
- Suppressed by policy rules: ${suppressed_policy_count}
- Suppressed by baseline snapshot: ${suppressed_baseline_count}
REPORT
