# Security Policy

## Supported Versions

| Version | Supported          |
|---------|--------------------|
| 0.x     | :white_check_mark: |

## Reporting a Vulnerability

**Do not report security vulnerabilities through public GitHub issues.**

Please send vulnerability reports to **security@ekklesia.app** with:

- Description of the vulnerability
- Steps to reproduce
- Potential impact assessment
- Suggested fix (if any)

## Response Timeline

- **Acknowledgment:** within 48 hours
- **Initial assessment:** within 7 days
- **Fix development:** within 30 days for critical issues
- **Public disclosure:** 90 days after report, or when the fix is released (whichever comes first)

## Security Update Process

1. Security patches are applied to the latest release
2. A security advisory is published on GitHub after the fix is released
3. All known affected tenants are notified directly

## Scope

The following are in scope for security reports:

- Cross-tenant data access or leakage
- Authentication or authorization bypass
- SQL injection, XSS, or other OWASP Top 10 vulnerabilities
- Sensitive data exposure (member PII, giving records)
- API abuse or rate limiting bypass

## Out of Scope

- Denial of service attacks
- Social engineering
- Issues in third-party dependencies (report to the upstream project)
- Issues requiring physical access to the server
