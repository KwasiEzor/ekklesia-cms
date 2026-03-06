#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/common.sh"

report="artifacts/baseline-update.md"
policy_file="scripts/ai-gates/policy.json"
baseline_file="scripts/ai-gates/baseline.json"

scripts/ai-gates/validate_policy.sh >/dev/null

php_files="$(rg --files app routes config 2>/dev/null | rg '\.php$' || true)"
if [[ -z "$php_files" ]]; then
  cat > "$baseline_file" <<JSON
{
  "suppress_fingerprints": []
}
JSON
  write_report "$report" "PASS" "AI Gate Baseline Update" <<'REPORT'
## Summary
- No PHP files found in app/routes/config.
- Baseline reset to empty list.
REPORT
  exit 0
fi

tenant_json="$(printf '%s\n' "$php_files" | php scripts/ai-gates/analyzers/tenant_isolation_audit.php)"
security_json="$(printf '%s\n' "$php_files" | php scripts/ai-gates/analyzers/security_review.php)"

tenant_filtered="$(php scripts/ai-gates/analyzers/filter_findings.php --gate tenant_isolation --policy "$policy_file" <<< "$tenant_json")"
security_filtered="$(php scripts/ai-gates/analyzers/filter_findings.php --gate security_review --policy "$policy_file" <<< "$security_json")"

tenant_tmp="$(mktemp)"
security_tmp="$(mktemp)"
printf '%s' "$tenant_filtered" > "$tenant_tmp"
printf '%s' "$security_filtered" > "$security_tmp"

# Build unique fingerprint list from currently active findings (after policy filters, before baseline suppression).
all_fingerprints="$(php -r '
$tenant=json_decode(file_get_contents($argv[1]), true);
$security=json_decode(file_get_contents($argv[2]), true);
$set=[];
foreach ([$tenant, $security] as $r) {
  foreach (($r["active_findings"] ?? []) as $f) {
    $fp=(string)($f["fingerprint"] ?? "");
    if ($fp !== "") { $set[$fp]=true; }
  }
}
$out=array_keys($set);
sort($out);
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
' "$tenant_tmp" "$security_tmp")"

rm -f "$tenant_tmp" "$security_tmp"

cat > "$baseline_file" <<JSON
{
  "suppress_fingerprints": ${all_fingerprints}
}
JSON

count="$(php -r '$b=json_decode(file_get_contents($argv[1]),true); echo count($b["suppress_fingerprints"] ?? []);' "$baseline_file")"

write_report "$report" "PASS" "AI Gate Baseline Update" <<REPORT
## Summary
- Baseline updated from current active findings (policy-filtered).
- Fingerprints stored: ${count}
- File: ${baseline_file}
REPORT
