# AI Skill Operations Plan

This document defines how AI skills are integrated into Ekklesia to improve product quality, design quality, and platform security without weakening tenant isolation.

---

## Goals

- Keep all AI-assisted development decisions auditable and human-approved.
- Enforce tenant isolation and security controls at PR time and pre-release time.
- Raise UI quality to a consistent, modern, clean standard across Filament surfaces.
- Reduce regressions by requiring test and contract coverage for risky changes.

---

## Enforcement Model

- **PR checks (blocking):** must pass before merge.
- **Scheduled checks (non-blocking alert):** nightly and weekly drift/security detection.
- **Release checks (blocking):** run on release branch/tag.
- **Human approval:** no AI action can merge code, apply labels, or publish comments automatically.

---

## Skill Matrix

| Skill | Trigger | Scope | Pass/Fail Rule | Output Artifact |
|---|---|---|---|---|
| Tenant Isolation Auditor | `pull_request`, `push` to `main` | Models, queries, jobs, exports, AI context builders | **Fail** if unscoped DB access or cross-tenant context risk is detected | `artifacts/tenant-audit.md` |
| Laravel Security Reviewer | `pull_request` | Validation, authorization, uploads, auth/token flows | **Fail** on High/Critical findings; Medium opens warning comment | `artifacts/security-review.md` |
| Prompt Safety Guard | `pull_request`, nightly | Assistant prompts, context serialization, redaction logic | **Fail** if prompt-injection protections or redaction checks regress | `artifacts/prompt-safety.md` |
| API Contract Guardian | `pull_request` | Controllers, requests, resources, Scramble docs | **Fail** if endpoint/request/response contracts drift without docs/test updates | `artifacts/api-contract.md` |
| PR Risk & Regression Reviewer | `pull_request` | Changed code paths and impact map | **Fail** if risk score `>= 8` and no matching tests added | `artifacts/pr-risk.md` |
| Pest Coverage Gap Finder | `pull_request` | Diff coverage + critical path coverage | **Fail** if changed lines coverage `< 85%` for critical modules | `artifacts/test-gaps.md` |
| Filament UX Consistency Critic | `pull_request` label `ui` or changed `app/Filament/**` | Forms, tables, states, responsive behavior, a11y basics | **Fail** if required UX checklist items are missing | `artifacts/ux-review.md` |
| Dependency Supply Chain Watcher | weekly schedule + Dependabot PR | Composer/npm dependency deltas | **Fail** on known exploitable CVEs; otherwise warning | `artifacts/dependency-watch.md` |
| Docs/Changelog Sync Agent | `pull_request` | Docs for architecture/behavior changes | **Fail** if behavior changed and changelog/docs not updated | `artifacts/docs-sync.md` |
| Policy Validation Gate | `pull_request`, `push` | AI gate allowlist policy file | **Fail** if policy schema/rules are invalid or too broad | `artifacts/policy-validation.md` |
| Drift Report | weekly schedule | Whole-repo tenant/security finding drift snapshot | Informational report for trend review | `artifacts/drift-report.md` |

---

## PR Quality Gates (Required Status Checks)

Required checks on `main` branch protection:

1. `ci/tests` (existing CI workflow)
2. `ai/policy-validation`
3. `ai/tenant-isolation-audit`
4. `ai/security-review`
5. `ai/prompt-safety`
6. `ai/api-contract-guardian`
7. `ai/pr-risk-and-tests`
8. `ai/test-gaps`
9. `ai/ux-consistency` (conditional; required when UI files change)
10. `ai/docs-sync`

Merge rule:

- Block merge on any failed required check.
- Block merge if any required check is skipped unexpectedly.
- Allow merge with warnings only when all required checks pass.
- `ai/pr-summary-comment` should remain informational (not required), since it runs with `if: always()`.

---

## Implementation Phases

## Phase 1 (Week 1): Security Baseline

Implement first:

1. Tenant Isolation Auditor
2. Laravel Security Reviewer
3. Prompt Safety Guard
4. Dependency Supply Chain Watcher

Definition of done:

- Blocking checks wired in GitHub Actions.
- Findings written as artifacts and PR summary comments.
- At least one regression test added per blocked class of issue.

## Phase 2 (Week 2): Engineering Quality Baseline

Implement:

1. PR Risk & Regression Reviewer
2. Pest Coverage Gap Finder
3. API Contract Guardian

Definition of done:

- Risk scoring appears on all PRs.
- Diff coverage threshold enforced for critical modules (`app/`, `routes/api.php`, `tests/Feature/Api/**`).
- Contract drift failures include exact endpoint and schema deltas.

## Phase 3 (Week 3): Product and Design Baseline

Implement:

1. Filament UX Consistency Critic
2. Docs/Changelog Sync Agent

Definition of done:

- UI PRs include checklist validation (states, spacing, responsive behavior, basic a11y).
- Architecture-affecting PRs require docs updates in `docs/architecture/**` and changelog entry.

---

## Governance Rules

- AI outputs are advisory plus gate signals, never auto-merge actions.
- High/Critical security findings require explicit maintainer resolution note before merge.
- Any tenant-isolation finding is treated as a release blocker.
- AI reviewer prompts and policies are versioned in repository and reviewed like code.

---

## KPI Targets

- Production incident rate from auth/tenant leaks: **0**
- PRs with required test coverage on changed critical paths: **>= 95%**
- Escaped regressions from modified API endpoints: **< 2% per month**
- UI review rework after merge: **< 10% of UI PRs**
- Mean time to triage AI security findings: **< 24h**

---

## Minimum Workflow Skeleton

Add a dedicated workflow file (example name: `.github/workflows/ai-quality-security.yml`) with these jobs:

1. `tenant_isolation_audit`
2. `security_review`
3. `prompt_safety`
4. `api_contract_guardian`
5. `pr_risk_and_tests`
6. `ux_consistency`
7. `docs_sync`

Each job must:

- check out code,
- install dependencies,
- run one deterministic analyzer command/script,
- upload an artifact report,
- fail with non-zero exit code on policy violations.

---

## Local Developer Workflow

Before opening a PR:

1. `composer check-all`
2. run tenant isolation tests for touched modules
3. run security-focused feature tests for touched auth/upload/AI paths
4. update `docs/guide/changelog.md` for behavior changes

This keeps local and CI quality gates aligned and avoids late-stage failures.

---

## Current Repository Wiring

The baseline gate wiring currently lives at:

- `.github/workflows/ai-quality-security.yml`
- `scripts/ai-gates/*.sh`
- `scripts/ai-gates/analyzers/*.php`
- `scripts/ai-gates/policy.json`
- `scripts/ai-gates/policy.template.json`
- `scripts/ai-gates/baseline.json`

Each gate script writes a markdown artifact under `artifacts/*.md` and exits non-zero on policy violation.
Tenant and security gates also emit inline GitHub annotations (`::error` / `::warning`) for unsuppressed findings.
Tenant and security gates also export SARIF (`artifacts/*.sarif`) for GitHub code scanning visibility.
PR runs publish one sticky summary comment with all gate statuses.
Policy thresholds drive fail behavior for tenant/security severity and diff-coverage minimum.
Baseline snapshot mode suppresses known finding fingerprints via `scripts/ai-gates/baseline.json`.
