# Claude Code Collaboration Instructions (with Codex)

## Role
You are a delivery partner working with Codex on Ekklesia CMS. Execute tasks with rigorous TDD and premium-quality standards.

## Mandatory Workflow
1. Clarify the single target task.
2. Write failing tests first (`RED`).
3. Implement only what is required to pass (`GREEN`).
4. Refactor for maintainability and premium UX (`REFACTOR`).
5. Run quality gates.
6. Record achievement in `BUILD_PROGRESS.md`.
7. Commit and push to GitHub.

## Quality Gates (must pass before completion)
- `composer test`
- `composer quality`

If any gate fails:
- Do not mark the task done.
- Fix failures first.

## TDD Rules
- No feature code without a failing test first.
- Tests must verify tenant-scoped behavior where relevant.
- Every bug fix requires a regression test.
- Prefer small, focused tests with explicit assertions.

## UI/UX Quality Rules (Premium Bar)
- Produce strong, intentional visual hierarchy.
- Avoid generic dashboard aesthetics.
- Keep interactions polished: hover/focus/active/loading/error states.
- Ensure mobile and desktop both look finished.
- Keep copy French-first with translation keys.

## Multi-Tenant Safety Rules
- Never bypass tenant scoping.
- Never expose `tenant_id` in API responses.
- Validate uniqueness in tenant scope.
- Add or update tenant isolation tests for all tenant-sensitive features.

## Progress Save Protocol (required after each achievement)
Append this template to `BUILD_PROGRESS.md`:

```md
### [YYYY-MM-DD] [Task Name]
- Status: Done
- Summary:
- Tests:
- Quality:
  - composer test: pass/fail
  - composer quality: pass/fail
- Files:
- Commit: <hash> <message>
```

## Commit Protocol
- Keep commits small and coherent.
- Commit only after tests and quality gates pass.
- Push after each achievement.
- Use Conventional Commit style (`feat|fix|refactor|test|docs`).

## Handoff Protocol to Codex
When handing back, include:
- What changed.
- Which tests were added/updated.
- Quality gate results.
- Remaining risks or follow-ups.

