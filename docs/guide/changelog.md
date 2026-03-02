# Changelog

## Phase 4 — Deployment Preparation (March 2026)

### API Tenancy Middleware
- **`InitializeTenancyByUser`** middleware: initializes tenant context from authenticated user's `tenant_id` for all authenticated API routes
- **`InitializeTenancyByHeader`** middleware: initializes tenant from `X-Tenant-ID` header for public routes (login, register)
- Ensures proper tenant isolation on all API endpoints in production

### Tenant Provisioning
- **`php artisan tenant:create`** command: creates tenant with domain, subdomain, and initial admin user
- Validates inputs (slug format, uniqueness, password strength)
- Supports custom domain assignment and auto-generates subdomain

### Demo Content Seeder
- **`TenantContentSeeder`**: seeds realistic church content for any tenant
- 10 sermons with Francophone African church context
- 5 events (Sunday service, prayer vigil, women's conference, youth camp, baptism)
- 4 announcements (pinned, active, expired, leader-targeted)
- 25 members (20 active + 5 visiting)
- 4 pages with block content (Accueil, À propos, Contact, Déclaration de foi)
- Giving records linked to members + anonymous donations in XOF

### Health Check
- **`GET /health`** endpoint: checks database and cache connectivity
- Returns `healthy` or `degraded` status with individual check details
- Used by Docker HEALTHCHECK and load balancers

### Docker & Deployment
- **Dockerfile**: multi-stage build with FrankenPHP + PHP 8.4 + Laravel Octane
- **docker-compose.yml**: app + PostgreSQL 16 + queue worker
- Production PHP config with OPcache, upload limits, security settings
- `.dockerignore` for lean production images

### Stats
- 194 tests passing (604 assertions)
- PHPStan level 5, Rector PHP 8.4, Pint — all green

## Phase 3 — API Layer (February 2026)

### Authentication Endpoints
- `POST /api/v1/auth/register` — create user account with tenant-scoped email uniqueness
- `POST /api/v1/auth/login` — authenticate and receive Sanctum bearer token
- `POST /api/v1/auth/logout` — revoke current token
- `GET /api/v1/auth/me` — get authenticated user profile (no tenant_id or password exposed)

### Token Management
- `GET /api/v1/auth/tokens` — list user's active tokens
- `DELETE /api/v1/auth/tokens/{id}` — revoke a specific token
- `DELETE /api/v1/auth/tokens` — revoke all tokens

### API Documentation
- Installed `dedoc/scramble` for auto-generated OpenAPI documentation
- Interactive docs available at `/docs/api` with Stoplight Elements UI
- All 20 endpoints documented with request/response schemas

### Cleanup
- Extracted Gallery validation into `StoreGalleryRequest` and `UpdateGalleryRequest` form request classes
- Added `lang/fr/auth.php` and `lang/en/auth.php` translation files
- Auth endpoints use `throttle:auth` (5 req/min per IP)

### Stats
- 191 tests passing (593 assertions)
- 16 new auth tests (register, login, logout, me, tokens, rate limiting, tenant isolation)

## Phase 2.5 — Hardening Sprint (February 2026)

### Code Quality Tooling
- **Rector PHP** configured for PHP 8.4 with DeadCode, CodeQuality, TypeDeclaration, and EarlyReturn rule sets — 54 files auto-refactored
- **PHPStan + Larastan** at level 5 with baseline for framework false positives — passes clean
- **GitHub Actions CI** pipeline: Pint + PHPStan + Rector + Pest on every push/PR with PostgreSQL 16 service container

### Security Hardening
- **Security headers middleware** (`SecurityHeaders.php`): X-Frame-Options, X-Content-Type-Options, HSTS, Referrer-Policy, Permissions-Policy
- **Tenant-aware rate limiting**: 60 req/min per tenant+IP on API, 5 req/min per IP on auth
- **Sanctum hardening**: token expiration set to 24 hours, `ekklesia_` token prefix for secret scanning
- **Tests:** 2 new security tests (headers verification + rate limiting 429 response)

### Governance
- `SECURITY.md` — vulnerability disclosure policy with 90-day responsible disclosure
- `CONTRIBUTING.md` — development standards, PR checklist, code quality requirements

### Stats
- 175 tests passing (549 assertions)
- PHPStan level 5, Rector PHP 8.4, Pint — all green

## Phase 2 — Core Content Types (February 2026)

### Session 6 — GivingRecord (2026-02-26)
- **Model:** `App\Models\GivingRecord` with `BelongsToTenant`, `HasSoftVersioning`, `LogsActivityWithTenant`
- **Relationships:** `belongsTo(Member)` — nullable for anonymous giving
- **Filament resource:** `GivingRecordResource` under "Finance" navigation group, currency selector (XOF/XAF/EUR/USD/GBP/CAD), payment methods (Mobile Money, Espèces, Virement, Carte)
- **API:** `GET/POST/PUT/DELETE /api/v1/giving-records` with filters: `?method=`, `?currency=`, `?member_id=`, `?anonymous=true`, `?campaign_id=`, `?from=`/`?to=` date range
- **Validation:** tenant-scoped member_id exists check, method must be in allowed list (mobile_money, cash, bank_transfer, card)
- **Computed:** `is_anonymous` accessor, `formatted_amount` accessor (e.g. "50 000,00 XOF")
- **Tests:** 9 unit + 14 API feature + 3 tenant isolation = 26 new tests (173 total passing)
- **Translations:** `lang/fr/giving_records.php` and `lang/en/giving_records.php`

### Session 5 — Page (2026-02-26)
- **Model:** `App\Models\Page` with `BelongsToTenant`, `HasSlug`, `HasSoftVersioning`, `LogsActivityWithTenant`
- **Block builder:** 6 block types — heading, rich_text, image, video, call_to_action, quote
- **Filament resource:** `PageResource` with `Builder` component, collapsible/reorderable blocks, SEO section, draft/published badge
- **API:** `GET/POST/PUT/DELETE /api/v1/pages` with `?published=true` filter, `?search=` title search, `NULLS LAST` sort
- **Validation:** tenant-scoped slug uniqueness, content_blocks array structure validation
- **Tests:** 8 unit + 10 API feature + 3 tenant isolation = 21 new tests (147 total passing)
- **Translations:** `lang/fr/pages.php` and `lang/en/pages.php` — all block type labels included

### Session 4 — Member & Gallery (2026-02-26)
- **Member model** with `HasStates` (MemberStatus state machine), `InteractsWithMedia` (avatar collection), `BelongsToTenant`
- **Gallery model** with polymorphic `galleryable` (linked to Event or Member), media collection for photos
- Integrated `spatie/laravel-sluggable`, `spatie/laravel-activitylog`, `spatie/laravel-model-states`, `spatie/laravel-tags`
- **API:** Full CRUD for both members and galleries with tenant-scoped filters
- **Tests:** 126 total passing after Session 4

### Session 3 — Announcement (2026-02-25)
- **Announcement model** with `published_at`/`expires_at` lifecycle, `pinned` flag, `target_group` filtering
- **Filament resource** with active/expired filters, target group selector
- **API:** filtering by `?pinned=`, `?active=`, `?expired=`, `?target_group=`
- **Tests:** 12 API + 3 isolation + 9 unit tests

### Session 2 — Event (2026-02-25)
- **Event model** with `start_at`/`end_at` datetime, `is_upcoming`/`is_past` accessors, `capacity` field
- **Filament resource** with upcoming/location filters
- **API:** filtering by `?upcoming=true`, `?location=`
- **Tests:** 11 API + 3 isolation + 6 unit tests

### Session 1 — Sermon (2026-02-25)
- **Sermon model** with `speaker`, `duration`, `series_id`, relational tags via `spatie/laravel-tags`
- **SermonSeries model** for grouping sermons
- **Filament resource** with speaker filter, formatted duration
- **API:** filtering by `?speaker=`
- **Tests:** 9 API + 3 isolation + 7 unit tests
- Real-time broadcasting via Laravel Reverb: `ContentChanged` event, `ContentObserver`, tenant-scoped private channels

## Phase 1 — Project Scaffold (February 2026)

### Added
- Laravel 12 project with PHP 8.4, Filament v5, PostgreSQL 16
- `stancl/tenancy` v3 single-database mode with `BelongsToTenant` trait
- `HasSoftVersioning` trait for one-level undo on all content types
- `LogsActivityWithTenant` trait for tenant-scoped activity logging
- Sanctum API authentication, versioned routes at `/api/v1/`
- French/English translation files for all resources

## v0.1.0 — Architecture Phase (February 2026)

### Decided
- Content versioning strategy: soft versioning with `previous_version` JSONB column and `HasSoftVersioning` trait

### Added
- Project vision and niche definition documented
- Multi-tenancy architectural decision: single database + `stancl/tenancy`
- Content type system decision: hybrid fixed columns + JSONB
- Full tech stack finalized: PHP 8.4, Laravel 12, Filament v5, PostgreSQL
- Architecture document v1.0 published
- VitePress documentation site launched
- Deployment strategy: Laravel Cloud (demo) + Sevalla (production)
- AI architecture: internal agents + user-facing assistant defined
- Business model: open core with premium hosting platform
