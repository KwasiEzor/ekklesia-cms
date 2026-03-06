# AI Gates Self-Test Checklist

Use this checklist to validate AI quality/security gates on a non-production test branch.

## Preconditions

1. Open a branch from `main`.
2. Ensure GitHub branch protection includes required AI checks.
3. Create a draft PR targeting `main`.

## Test 1: Policy Validation Gate

Goal: verify `ai/policy-validation` fails on invalid policy.

1. Edit `scripts/ai-gates/policy.json` with an invalid broad rule:

```json
{
  "allowlist": [
    {
      "gate": "*",
      "code": "*",
      "reason": "too broad"
    }
  ]
}
```

2. Push and confirm:
- `ai/policy-validation` fails.
- `artifacts/policy-validation.md` explains why.

3. Restore valid policy:

```json
{
  "allowlist": []
}
```

## Test 2: Tenant Isolation Gate + Annotations + SARIF

Goal: verify `ai/tenant-isolation-audit` fails, creates annotation, and appears in code scanning.

1. Add a temporary line in a test branch PHP file (for example a temporary method in any service/controller):

```php
DB::table('users')->count();
```

2. Push and confirm:
- `ai/tenant-isolation-audit` fails.
- Inline `::error` annotation appears on the file/line.
- SARIF appears in GitHub Code Scanning under category `ai/tenant-isolation-audit`.

3. Remove the temporary line and push.

## Test 3: Security Review Gate + Annotations + SARIF

Goal: verify `ai/security-review` catches high-severity mass assignment pattern.

1. Add a temporary unsafe line:

```php
$model->update($request->all());
```

2. Push and confirm:
- `ai/security-review` fails.
- Inline annotation appears.
- SARIF appears in Code Scanning under `ai/security-review`.

3. Remove the temporary line and push.

## Test 4: PR Summary Comment

Goal: verify sticky summary comment is created/updated.

1. After running at least one failing and one passing check, confirm:
- a comment with marker `<!-- ai-gates-summary -->` exists,
- re-push updates the same comment (no duplicates),
- statuses reflect latest run.

## Test 5: Test Gaps Coverage Gate

Goal: verify `ai/test-gaps` fails when changed critical lines are uncovered.

1. Change logic in `app/` without adding/updating test coverage.
2. Push and confirm `ai/test-gaps` fails.
3. Add coverage test and push again; confirm `ai/test-gaps` passes.

## Cleanup

1. Remove all temporary unsafe test lines.
2. Ensure `scripts/ai-gates/policy.json` is restored.
3. Confirm PR is green.
4. Squash or drop self-test commits before merging.

## Expected Gate Signals

- Blocking checks fail on policy/security/tenant/coverage violations.
- Code scanning receives SARIF for tenant/security gates.
- PR summary comment always updates to latest status.
