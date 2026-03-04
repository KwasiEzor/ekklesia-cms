# Ekklesia CMS — Codex x Claude Execution Plan

## Purpose
Build Ekklesia CMS with strict TDD, premium product quality, and reliable progress tracking after every achievement.

## Non-Negotiable Standards
- TDD always: `RED -> GREEN -> REFACTOR`.
- Tenant isolation is never optional.
- French-first UX with English fallback.
- Visual output must feel premium, intentional, and production-grade.
- No milestone is complete until tests and quality gates pass.

## Working Mode
1. Pick one small vertical slice (single feature or hardening task).
2. Write or extend failing tests first.
3. Implement minimum code to pass tests.
4. Refactor for clarity, maintainability, and design quality.
5. Run full quality gates.
6. Save progress in `BUILD_PROGRESS.md`.
7. Commit and push.

## Phase Order (Immediate)
1. Hardening backlog
- Fix `composer quality` failures (`pint`, `phpstan`, `rector --dry-run`).
- Reduce type-safety warnings and dynamic-property ambiguity.

2. Premium UI/UX pass
- Create a coherent visual system (color, typography, spacing, states).
- Upgrade key admin surfaces: dashboard, settings, resource forms/tables.
- Enforce responsive behavior and accessibility quality.

3. Premium modules completion
- Payments, notifications, billing limits, campus experience.
- Add tests for critical paths, tenant scope, and failure modes.

4. Deployment readiness
- Finalize docs, release checklist, operational runbook.

## TDD Definition of Done
A task is done only when all conditions are true:
- Unit tests added/updated and passing.
- Feature tests added/updated and passing.
- Tenant isolation tests added/updated and passing when applicable.
- `composer test` passes.
- `composer quality` passes (`pint --test`, `phpstan`, `rector --dry-run`).
- UI acceptance checks complete for visual tasks.
- Progress entry added to `BUILD_PROGRESS.md`.
- Commit pushed to GitHub.

## Premium UI Acceptance Checklist
- Strong hierarchy and spacing rhythm.
- Clear component states (default, hover, focus, disabled, loading, error).
- Accessible contrast and keyboard-friendly interactions.
- Consistent iconography and tone across pages.
- Mobile and desktop layouts both polished.
- No placeholder-feel visuals.

## Progress Logging Rule (After Every Achievement)
After each completed slice, append an entry in `BUILD_PROGRESS.md`:

```md
### [YYYY-MM-DD] [Phase/Task]
- Status: Done
- Goal:
- Tests added/updated:
- Quality checks:
  - composer test: pass/fail
  - composer quality: pass/fail
- Files touched:
- Risks/notes:
- Commit: <hash> <message>
```

## Commit and Push Protocol
- Commit scope: one vertical slice per commit.
- Commit message format:
  - `feat: ...` for features
  - `fix: ...` for bug fixes
  - `refactor: ...` for internal cleanup
  - `test: ...` for test-focused updates
  - `docs: ...` for documentation updates
- Push after each validated achievement.

