# Changelog

## Production Readiness Sprint (March 2026)

### Features 1–9: Complete Production Feature Set

**9 new features** fully implemented with models, migrations, Filament resources, API endpoints, factories, policies, translations, and comprehensive test coverage.

#### 1. Attendance Tracking
- **Models:** `ServiceType`, `Attendance`
- **Filament:** `ServiceTypeResource`, `AttendanceResource` with service type filters
- **API:** Full CRUD for both service types and attendance records
- **Tests:** Unit, API, and tenant isolation tests

#### 2. Family/Household Linking
- **Model:** `Household` with head of household, family roles
- **Member update:** `household_id`, `family_role`, `date_of_birth` columns
- **Filament:** `HouseholdResource` with member listing
- **API:** Full CRUD with family member aggregation

#### 3. Fund & Campaign Management
- **Models:** `Fund`, `Campaign` with goal tracking and progress calculation
- **GivingRecord update:** `fund_id` foreign key for categorized giving
- **Filament:** `FundResource`, `CampaignResource` with real-time totals
- **Multi-currency support** for global church operations

#### 4. Prayer Wall & Tracking
- **Models:** `PrayerRequest`, `PrayerCommitment`
- **Features:** Public/group/confidential visibility, "Je prie" commitments, answered prayer tracking
- **Filament:** `PrayerRequestResource` with moderation workflow
- **API:** Prayer requests with commitment and answer endpoints

#### 5. Daily Devotional Delivery
- **Models:** `Devotional`, `DevotionalSeries`
- **Features:** Verse reference, reflection, prayer point, application fields
- **Series support** for themed multi-day devotionals
- **Scheduling system** for advance creation and publishing

#### 6. Testimony Sharing
- **Model:** `Testimony` with categories (healing, provision, deliverance, conversion, family restoration)
- **Features:** Moderation workflow (submitted → review → approved/rejected), anonymous option, culturally appropriate reactions (Amen, Gloire à Dieu, Alléluia)
- **Audio recording support** via Spatie Media Library

#### 7. Birthday Auto-Notifications
- **Command:** `SendBirthdayNotifications` — scans all tenants daily
- **Notifications:** `BirthdayNotification`, `AnniversaryNotification`
- **Member scopes:** `birthdayToday`, `birthdayThisWeek`, `anniversaryToday`
- **Accessors:** `age`, `years_married`

#### 8. Bible Reading Plans & Streaks
- **Models:** `ReadingPlan`, `ReadingPlanDay`, `MemberReadingProgress`
- **Features:** Daily passage tracking, streak counter with grace period, longest streak tracking
- **API:** Subscribe, complete day, progress with streak data

#### 9. SMS Bulk Messaging
- **Models:** `BulkMessage`, `MessageTemplate`
- **Job:** `SendBulkMessageJob` with per-recipient dispatch tracking
- **Command:** `SendScheduledBulkMessages` for scheduled delivery
- **Targeting:** All members, cell group, campus, or status-based audiences

### Security & Financial Hardening
- **Immutable financial records:** `GivingRecord` and `PaymentTransaction` block updates/deletes at model level
- **Adjustment system:** Polymorphic `Adjustment` model for voids and corrections
- **PII scrubbing:** `LogsActivityWithTenant` automatically redacts sensitive fields in audit logs
- **AI rate limiting:** 10 messages/minute per tenant via `RateLimiter`
- **Audit trail:** Read-only `ActivityResource` for administrators

### Page Builder "Pro" Update
- **Dynamic Blocks:** `sermon_feed` with filterable dynamic data, `staff_directory` mapped to actual system users/roles, `events_feed` with logic filtering, and `giving_cta` tied to dynamic funds.
- **Motion & Interactions:** Carousels for `hero`, infinite marquee loops for `logo_cloud`, interactive `tabs` and `newsletter_signup`.
- **Engagement Logic:** `live_stream` banner linked to global `live_stream_active` tenant setting for true automation.
- **Premium Layouts:** Advanced `columns` with nested structures, `mosaic` layout for `gallery`, and visual `waves` for `divider` blocks.

### Observability
- **`spatie/laravel-health`** monitoring: Database, Storage, Debug Mode, Environment, App Optimization
- **Admin runbook** for church staff (Treasurers, Pastors)

### Data Portability
- **CSV exporters** for GivingRecords and Members via Filament
- **Responsive images** for Member avatars and Gallery photos

### Stats
- 568 tests passing (1561 assertions)
- 15 new models, 18 new migrations, 10 new API controllers
- Full French translations for all 9 features

---

## Phase 5 — AI Layer (March 2026)

### Multi-Provider AI Assistant
- **AiManager** driver pattern: Claude, OpenAI, Gemini
- **14 specialized skills** in 5 categories: Content Creation, Church Management, Design & Branding, Security & Maintenance, AI Guidance
- **Tenant-scoped context pipeline** with PII filtering and cached aggregate stats
- **Streaming responses** via Laravel Reverb on private `ai-chat.{userId}` channel
- **Per-tenant configuration:** provider, model, API key, max tokens

### Stats
- 228 tests passing (34 new)

---

## UI Refonte — Premium Admin Redesign (March 2026)

### Dashboard Widgets
- StatsOverview (4 stat cards with sparklines), GivingChart, MemberDistributionChart, UpcomingEventsWidget, SermonsChart

### Settings Page — 7 Tabs
- Church Information, Appearance & Design, Social Media, SEO & Analytics, Notifications, Modules, Advanced

### Resource Form Redesign
- Icon sections with descriptions, collapsible secondary content, prefix icons, placeholders, helper texts across all 7 resources

### RBAC & Shield Integration
- 4 base roles: Super Admin, Pastor, Treasurer, Volunteer
- `filament/shield` v4 with tenant-scoped permissions
- Policies for all 10+ core resources

---

## Phase 4 — Deployment Preparation (March 2026)

### AI Quality & Security Governance
- Added architecture runbook: [AI Skill Operations Plan](/architecture/ai-skill-ops)
- Defines AI quality/security gates for tenant isolation, security review, prompt safety, API contract drift, PR risk, test coverage, UX consistency, dependency watch, docs sync, and policy validation
- Introduces blocking vs non-blocking AI checks and rollout phases with measurable KPIs
- Added workflow `.github/workflows/ai-quality-security.yml` with separate AI gate status checks
- Upgraded tenant/security gates to AST-based PHP analysis and added changed-lines coverage gate (`ai/test-gaps`)
- Added policy-driven allowlist file (`scripts/ai-gates/policy.json`) and inline CI annotations for unsuppressed findings
- Added strict policy validation gate (`ai/policy-validation`) and starter template (`scripts/ai-gates/policy.template.json`)
- Added SARIF export/upload for tenant and security gates to feed GitHub code scanning alerts
- Added sticky PR summary comment job (`ai/pr-summary-comment`) aggregating all gate statuses and artifact references
- Added team runbook: `docs/guide/ai-gates-self-test.md` for safe end-to-end gate verification
- Added baseline snapshot support (`scripts/ai-gates/baseline.json`) and updater (`scripts/ai-gates/update_baseline.sh`)
- Added policy-driven thresholds for tenant/security severity and diff-coverage minimum
- Added weekly drift reporting job (`ai/drift-report`) with artifact snapshot output
- Added analyzer unit tests for policy validation, baseline suppression, and SARIF conversion

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

### Stats (at the time)
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
