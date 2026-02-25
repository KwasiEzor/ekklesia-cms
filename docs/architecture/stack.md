# Technology Stack

## Runtime & Framework

| Layer | Package | Version | Role |
|-------|---------|---------|------|
| Runtime | PHP | 8.4 | Language — JIT enabled |
| Framework | Laravel | ^12.0 | Application backbone |
| Admin UI | Filament | ^5.0 | Admin panel, forms, tables |
| Reactivity | Livewire | ^3.0 | Real-time UI |
| Frontend JS | Alpine.js | ^3.0 | Lightweight interactivity |
| Styling | Tailwind CSS | v4 | Ships with Filament v5 |

## Data

| Layer | Package | Version | Role |
|-------|---------|---------|------|
| Database | PostgreSQL | 16+ | Primary DB — JSONB + tenant scoping |
| Multi-tenancy | stancl/tenancy | ^3.0 | Tenant identification and scoping |
| Media | spatie/laravel-medialibrary | ^11.0 | File uploads, conversions, S3/R2 |
| i18n | spatie/laravel-translatable | ^6.0 | Multilingual Eloquent models |
| ORM | Eloquent (Laravel) | built-in | Database abstraction |

## API & Auth

| Layer | Package | Version | Role |
|-------|---------|---------|------|
| API Auth | Laravel Sanctum | ^4.0 | Token-based API authentication |
| API Resources | Laravel API Resources | built-in | Response transformation |
| Rate Limiting | Laravel built-in | — | Per-tenant rate limits |

## Performance

| Layer | Package | Version | Role |
|-------|---------|---------|------|
| Server | Laravel Octane (FrankenPHP) | ^2.0 | Persistent process model |
| Cache | Laravel Cache (Redis) | built-in | Query and response caching |
| Queue | Laravel Queues (Redis) | built-in | Jobs, agents, notifications |

## Admin Features

| Layer | Package | Version | Role |
|-------|---------|---------|------|
| Permissions | filament/shield | latest | Role & permission management |
| Content Blocks | statikbe/flexible-content-blocks | latest | Block-based page builder |
| SEO | spatie/laravel-sitemap | latest | Sitemap generation |

## Frontend Starter

| Layer | Package | Version | Role |
|-------|---------|---------|------|
| SPA Bridge | Inertia.js | ^2.0 | Laravel ↔ React bridge |
| Frontend | React | 19 | Headless starter kit |
| Types | TypeScript | 5.x | Type safety |

## AI

| Layer | Package | Version | Role |
|-------|---------|---------|------|
| AI (internal) | Claude API (Anthropic) | claude-sonnet-4-6 | Maintenance agents |
| AI (user-facing) | Claude API (Anthropic) | claude-sonnet-4-6 | In-admin assistant |

## Deployment

| Provider | Use Case |
|----------|----------|
| Laravel Cloud | Demo environments — one-click, auto-expiry |
| Sevalla | Production — Google Cloud, African infrastructure |
| Self-hosted | Technical users — Laravel installer script |

## Why This Stack?

**PHP 8.4** brings the most mature JIT compilation in PHP's history, fiber support for async operations, and property hooks. Laravel 12 requires 8.2 minimum — we use 8.4 to take full advantage of performance improvements.

**Filament v5** is the current stable release. Its unified Schema system, 2-3x faster table rendering, built-in MFA, and nested resources make it the right foundation for a CMS that needs to be flexible and performant.

**PostgreSQL** over MySQL is non-negotiable for this project due to its JSONB capabilities. See [Core Decisions](/architecture/decisions) for the full reasoning.

**Claude API** for both AI layers because it offers the best multilingual performance for French and English, which is the primary language pair for Ekklesia's target audience.
