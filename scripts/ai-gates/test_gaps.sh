#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/common.sh"

report="artifacts/test-gaps.md"
policy_file="scripts/ai-gates/policy.json"
threshold="${AI_COVERAGE_THRESHOLD:-$(php -r '$p=json_decode(file_get_contents($argv[1]),true); echo $p["thresholds"]["test_gaps"]["min_coverage"] ?? 85;' "$policy_file")}"

tmp_err="$(mktemp)"
if ! json_output="$(php scripts/ai-gates/analyzers/diff_coverage.php --diff-range "$(get_diff_range)" --clover artifacts/clover.xml --threshold "$threshold" 2>"$tmp_err")"; then
  err_msg="$(cat "$tmp_err")"
  rm -f "$tmp_err"

  write_report "$report" "FAIL" "Pest Coverage Gap Finder" <<REPORT
## Changed-Lines Coverage
- Coverage: unavailable
- Threshold: ${threshold}%
- Notes: unable to compute diff coverage.

## Error
\`\`\`
${err_msg}
\`\`\`
REPORT
  exit 1
fi
rm -f "$tmp_err"

status="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo $r["status"] ?? "fail";' <<< "$json_output")"
percent="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo $r["percent"] ?? 0;' <<< "$json_output")"
covered="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo $r["covered"] ?? 0;' <<< "$json_output")"
total="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo $r["total"] ?? 0;' <<< "$json_output")"
message="$(php -r '$r=json_decode(stream_get_contents(STDIN),true); echo $r["message"] ?? "";' <<< "$json_output")"

files_md="$(php -r '
$r=json_decode(stream_get_contents(STDIN),true);
$files=$r["files"] ?? [];
if ($files === []) { echo "- No executable changed lines mapped to coverage statements.\n"; exit(0); }
foreach ($files as $f) {
  echo "- {$f["file"]}: {$f["covered"]}/{$f["total"]} ({$f["percent"]}%)\n";
}
' <<< "$json_output")"

if [[ "$status" == "fail" ]]; then
  write_report "$report" "FAIL" "Pest Coverage Gap Finder" <<REPORT
## Changed-Lines Coverage
- Coverage: ${percent}%
- Threshold: ${threshold}%
- Covered statements: ${covered}/${total}
- Notes: ${message}

## File Breakdown
${files_md}
REPORT
  exit 1
fi

write_report "$report" "PASS" "Pest Coverage Gap Finder" <<REPORT
## Changed-Lines Coverage
- Coverage: ${percent}%
- Threshold: ${threshold}%
- Covered statements: ${covered}/${total}
- Notes: ${message}

## File Breakdown
${files_md}
REPORT
