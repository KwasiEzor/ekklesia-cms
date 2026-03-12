# Roadmap

## Phase 0 — Architecture :white_check_mark:

> Status: **Complete** (February 2026)

- [x] Project vision and niche definition
- [x] Multi-tenancy strategy decision
- [x] Content type system decision
- [x] Tech stack finalization
- [x] Architecture document v1.0
- [x] Documentation site (this site)
- [x] Content versioning decision — soft versioning with `previous_version` JSONB
- [x] AI context pipeline design
- [x] Database schema v1 design
- [ ] Plugin architecture contract (deferred — not blocking)

---

## Phase 1 — Project Scaffold :white_check_mark:

> Status: **Complete** (February 2026)

- [x] Laravel 12 + Filament v5 project scaffold
- [x] Multi-tenancy with `stancl/tenancy` (single-database mode)
- [x] PostgreSQL 16 with JSONB custom fields
- [x] Sanctum API authentication
- [x] Spatie Media Library integration
- [x] French + English admin UI
- [x] `HasSoftVersioning` trait for one-level content undo
- [x] Real-time broadcasting via Laravel Reverb

---

## Phase 2 — Core Content Types :white_check_mark:

> Status: **Complete** (February 2026)

- [x] Sermon (model, migration, Filament resource, API, tests)
- [x] Event
- [x] Announcement
- [x] Member & Gallery (with Spatie Media Library)
- [x] Page (with block builder — 6 block types)
- [x] GivingRecord (multi-currency, mobile money)

---

## Phase 2.5 — Hardening Sprint :white_check_mark:

> Status: **Complete** (March 2026)

- [x] Rector PHP code quality rules
- [x] PHPStan level 5 static analysis
- [x] GitHub Actions CI pipeline
- [x] Security headers middleware
- [x] Tenant-aware API rate limiting
- [x] SECURITY.md and CONTRIBUTING.md

---

## Phase 3 — API Layer :white_check_mark:

> Status: **Complete** (March 2026)

- [x] Auth endpoints (login, logout, register, token management)
- [x] Full CRUD API for all 6 core content types
- [x] Scramble auto-generated API documentation
- [x] Route versioning `/api/v1/`
- [x] Tenant-scoped rate limiting

---

## Phase 4 — First Deployment :white_check_mark:

> Status: **Complete** (March 2026)

- [x] Tenancy middleware for API tenant resolution
- [x] `tenant:create` Artisan command
- [x] Demo content seeders
- [x] Health check endpoint
- [x] Docker + docker-compose configuration
- [x] FrankenPHP + Laravel Octane production setup

---

## Phase 5 — AI Layer :white_check_mark:

> Status: **Complete** (March 2026)

- [x] Multi-provider AI assistant (Claude, OpenAI, Gemini)
- [x] 14 specialized skills in 5 categories
- [x] Tenant-scoped context pipeline (no PII exposure)
- [x] Streaming responses via Laravel Reverb
- [x] AI configuration per tenant in settings

---

## Production Readiness Sprint :white_check_mark:

> Status: **Complete** (March 2026) — 9 features fully implemented and tested

| # | Feature | Status |
|---|---------|--------|
| 1 | Attendance Tracking (service types, QR check-in) | :white_check_mark: Complete |
| 2 | Family/Household Linking | :white_check_mark: Complete |
| 3 | Fund & Campaign Management | :white_check_mark: Complete |
| 4 | Prayer Wall & Tracking | :white_check_mark: Complete |
| 5 | Daily Devotional Delivery | :white_check_mark: Complete |
| 6 | Testimony Sharing | :white_check_mark: Complete |
| 7 | Birthday Auto-Notifications | :white_check_mark: Complete |
| 8 | Bible Reading Plans & Streaks | :white_check_mark: Complete |
| 9 | SMS Bulk Messaging | :white_check_mark: Complete |

---

## Phase 6 — Premium Modules :white_check_mark:

> Status: **Complete** (March 2026)

- [x] Page Builder "Pro" Update (Dynamic components, Motion, and Integrations)
- [x] Mobile money giving (FedaPay integration for MTN, Orange, Moov, Wave)
- [x] SMS pastoral notifications (Africa's Talking, Twilio, Log drivers)
- [x] WhatsApp integration (Driver-ready)
- [x] Multi-campus management (Campus scoping and filtering)
- [x] Exportable reports (CSV/Excel for Members and Giving)
- [x] Operations Dashboard (Real-time notification tracking and status)
- [x] System Observability (Spatie Health + custom AI/Redis checks)
- [ ] Premium hosting platform (ekklesia.app — planned)
- [ ] Billing system (Laravel Cashier integration — ready)

---

## Future Roadmap

> Target: 2027+

- [ ] Badges & achievements system
- [ ] Spiritual growth journey tracking
- [ ] Fasting & prayer challenges
- [ ] Recurring events
- [ ] Cell group leader portal
- [ ] Bible school / courses
- [ ] Children's check-in
- [ ] Member self-service portal
- [ ] Printable certificates
- [ ] React Native mobile app
- [ ] Local language support (Ewe, Lingala, Fon — community-driven)
- [ ] Plugin marketplace

---

::: tip Contribute
If you want to contribute to any phase, open an issue or discussion on [GitHub](https://github.com/KwasiEzor/ekklesia-cms). Feature contributions, translations, and bug reports are all welcome.
:::
