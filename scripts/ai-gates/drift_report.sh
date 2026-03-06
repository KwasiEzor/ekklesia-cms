#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/common.sh"

report="artifacts/drift-report.md"
json_report="artifacts/drift-report.json"
policy_file="scripts/ai-gates/policy.json"
baseline_file="scripts/ai-gates/baseline.json"

scripts/ai-gates/validate_policy.sh >/dev/null

php_files="$(rg --files app routes config 2>/dev/null | rg '\.php$' || true)"
if [[ -z "$php_files" ]]; then
  write_report "$report" "PASS" "AI Gate Drift Report" <<'REPORT'
## Summary
- No PHP files found in app/routes/config.
REPORT
  cat > "$json_report" <<'JSON'
{
  "tenant_isolation": {"active": 0, "suppressed": 0},
  "security_review": {"active": 0, "suppressed": 0},
  "allowlist_rules": 0,
  "baseline_fingerprints": 0
}
JSON
  exit 0
fi

tenant_json="$(printf '%s\n' "$php_files" | php scripts/ai-gates/analyzers/tenant_isolation_audit.php)"
security_json="$(printf '%s\n' "$php_files" | php scripts/ai-gates/analyzers/security_review.php)"

tenant_filtered="$(php scripts/ai-gates/analyzers/filter_findings.php --gate tenant_isolation --policy "$policy_file" --baseline "$baseline_file" <<< "$tenant_json")"
security_filtered="$(php scripts/ai-gates/analyzers/filter_findings.php --gate security_review --policy "$policy_file" --baseline "$baseline_file" <<< "$security_json")"

tenant_active="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo (int)($r["counts"]["active"] ?? 0);' <<< "$tenant_filtered")"
tenant_suppressed="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo (int)($r["counts"]["suppressed"] ?? 0);' <<< "$tenant_filtered")"
security_active="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo (int)($r["counts"]["active"] ?? 0);' <<< "$security_filtered")"
security_suppressed="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo (int)($r["counts"]["suppressed"] ?? 0);' <<< "$security_filtered")"
allowlist_rules="$(php -r '$p=json_decode(file_get_contents($argv[1]),true); echo count($p["allowlist"] ?? []);' "$policy_file")"
baseline_count="$(php -r '$b=json_decode(file_get_contents($argv[1]),true); echo count($b["suppress_fingerprints"] ?? []);' "$baseline_file")"

cat > "$json_report" <<JSON
{
  "tenant_isolation": {"active": ${tenant_active}, "suppressed": ${tenant_suppressed}},
  "security_review": {"active": ${security_active}, "suppressed": ${security_suppressed}},
  "allowlist_rules": ${allowlist_rules},
  "baseline_fingerprints": ${baseline_count}
}
JSON

write_report "$report" "PASS" "AI Gate Drift Report" <<REPORT
## Current Snapshot
- Tenant isolation active findings: ${tenant_active}
- Tenant isolation suppressed findings: ${tenant_suppressed}
- Security review active findings: ${security_active}
- Security review suppressed findings: ${security_suppressed}

## Policy Footprint
- Allowlist rules: ${allowlist_rules}
- Baseline fingerprints: ${baseline_count}

## Output
- JSON report: ${json_report}
REPORT
