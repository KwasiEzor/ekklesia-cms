# Ekklesia CMS — Build Progress Report

> Auto-updated as each phase and session completes.
> Repo: https://github.com/KwasiEzor/ekklesia-cms

---

## Phase Summary

| Phase | Description | Status | Date |
|-------|------------|--------|------|
| 0 | Architecture Completion | Done | 2026-02-25 |
| 1 | Project Scaffold | Done | 2026-02-25 |
| 2.1 | Sermon Content Type | Done | 2026-02-25 |
| 2.2 | Event Content Type | Done | 2026-02-25 |
| 2.3 | Announcement Content Type | Pending | — |
| 2.4 | Member Content Type | Pending | — |
| 2.5 | Page Content Type | Pending | — |
| 2.6 | GivingRecord Content Type | Pending | — |
| 3 | API Layer | Pending | — |
| 4 | First Deployment | Pending | — |
| 5 | AI Layer | Pending | — |
| 6 | Premium Modules | Pending | — |

---

## Phase 0 — Architecture Completion

**Commit:** `b3abd47` — `docs: resolve content versioning — soft versioning with previous_version JSONB`
**Date:** 2026-02-25

### Decisions Made

**Content Versioning — DECIDED: Soft versioning**
- Each content table gets a nullable `previous_version JSONB` column
- `App\Concerns\HasSoftVersioning` Eloquent trait snapshots dirty fields on `updating` event
- One-level undo in v1; upgrade path to full revision tables remains open
- Rationale: church staff are non-technical — accidental saves must be recoverable without the complexity of full revision tables

**Plugin Architecture — still OPEN** (deferred, not blocking Phase 1)

### Files Changed
- `docs/architecture/decisions.md` — added Content Versioning DECIDED section
- `docs/architecture/open-questions.md` — marked versioning as resolved
- `docs/guide/changelog.md` — added decision entry
- `CLAUDE.md` — checked off versioning, fixed Livewire ^3.0 → ^4.0

---

## Phase 1 — Project Scaffold

**Commit:** `9df01e3` — `feat: Laravel 12 scaffold with Filament v5, stancl/tenancy, PostgreSQL`
**Date:** 2026-02-25

### What Was Built

| Component | Detail |
|-----------|--------|
| Framework | Laravel 12 (v12.53.0) |
| Admin UI | Filament v5 with Livewire v4 |
| Multi-tenancy | stancl/tenancy v3 — single-database mode, `tenant_id` column scoping |
| Database | PostgreSQL 16 — databases: `ekklesia` (dev), `ekklesia_test` (test) |
| Media | spatie/laravel-medialibrary v11 |
| i18n | spatie/laravel-translatable v6 |
| API Auth | Laravel Sanctum v4 |
| Performance | Laravel Octane v2 (FrankenPHP) |
| Testing | Pest PHP v3 with pest-plugin-laravel |

### Key Configuration
- **Locale:** French primary (`APP_LOCALE=fr`), English fallback
- **Tenant resolution:** Filament panel uses `->tenant(Tenant::class, slugAttribute: 'slug')`
- **DatabaseTenancyBootstrapper:** Disabled (single-DB mode — scoping via `BelongsToTenant` trait)
- **DB creation/deletion jobs:** Removed from TenancyServiceProvider
- **VitePress docs:** Isolated to `docs/package.json` with own `node_modules`
- **GitHub Actions:** Updated to `working-directory: docs`; legacy Jekyll workflow deleted

### Models Created
- `App\Models\Tenant` — HasDatabase, HasDomains, HasFactory; custom columns: id, name, slug
- `App\Models\User` — BelongsToTenant, HasApiTokens; tenant_id foreign key + unique email per tenant

### Traits Created
- `App\Concerns\HasSoftVersioning` — snapshots changed fields into `previous_version` JSONB on update; `revertToPreviousVersion()` restores and clears

### Migrations
| Migration | Tables |
|-----------|--------|
| `0001_01_00_000000` | `tenants` (id, name, slug, data, timestamps) |
| `0001_01_00_000001` | `domains` (domain, tenant_id FK) |
| `0001_01_01_000000` | `users` (tenant_id FK, unique email per tenant), `password_reset_tokens`, `sessions` |
| `0001_01_01_000001` | `cache`, `cache_locks` |
| `0001_01_01_000002` | `jobs`, `job_batches`, `failed_jobs` |
| `2026_02_25_123804` | `media` (spatie/medialibrary) |
| `2026_02_25_124148` | `personal_access_tokens` (Sanctum) |

### Translation Files
- `lang/fr/navigation.php` — sidebar labels in French
- `lang/fr/common.php` — CRUD action labels in French
- `lang/en/navigation.php` — English fallback
- `lang/en/common.php` — English fallback

---

## Phase 2, Session 1 — Sermon Content Type

**Commit:** `874fde7` — `feat: Sermon content type — model, migration, Filament resource, API, tests`
**Date:** 2026-02-25

### Models
- **`App\Models\Sermon`** — BelongsToTenant, HasSoftVersioning, HasFactory
  - Casts: `date`, `duration` (int), `tags` (array), `custom_fields` (array), `previous_version` (array)
  - Hidden: `tenant_id`
  - Accessor: `formatted_duration` — converts seconds to `H:MM:SS` or `M:SS`
  - Relationship: `belongsTo(SermonSeries::class)`

- **`App\Models\SermonSeries`** — BelongsToTenant, HasFactory
  - Relationship: `hasMany(Sermon::class)`

### Migrations
| Migration | Table | Indexes |
|-----------|-------|---------|
| `2026_02_25_140000` | `sermon_series` | `tenant_id`, unique `(tenant_id, slug)` |
| `2026_02_25_140001` | `sermons` | `tenant_id`, `(tenant_id, created_at)`, `(tenant_id, date)`, unique `(tenant_id, slug)`, GIN on `custom_fields` |

### Filament Resource
- **`App\Filament\Resources\SermonResource`**
  - Navigation: icon `Heroicon::OutlinedMicrophone`, group "Contenu" (French)
  - Form: title (auto-slug), slug, speaker, date, duration, series (select), audio_url, video_url, tags, transcript
  - Table: title, speaker, date, series, formatted_duration; filter by series; default sort by date desc
  - Pages: ListSermons, CreateSermon, EditSermon

### API Layer
| Endpoint | Controller Method | Auth |
|----------|------------------|------|
| `GET /api/v1/sermons` | `index` | Sanctum |
| `POST /api/v1/sermons` | `store` | Sanctum |
| `GET /api/v1/sermons/{sermon}` | `show` | Sanctum |
| `PUT /api/v1/sermons/{sermon}` | `update` | Sanctum |
| `DELETE /api/v1/sermons/{sermon}` | `destroy` | Sanctum |

- **Filtering:** `?speaker=`, `?series_id=`, `?tag=`
- **Pagination:** `?per_page=` (default 15)
- **Response:** `SermonResource` / `SermonCollection` — never exposes `tenant_id`
- **Validation:** `StoreSermonRequest` / `UpdateSermonRequest` — tenant-scoped unique slug

### Tests — 23 passing (67 assertions)

**Unit (11 tests)**
- `SermonTest` — casts (custom_fields, tags), hidden tenant_id, formatted_duration (hours, minutes, null), belongs to series
- `HasSoftVersioningTest` — snapshot on update, revert restores previous, revert returns false when empty, hasPreviousVersion boolean

**Feature (12 tests)**
- `SermonApiTest` — 401 without auth, list, no tenant_id in response, create, show, update, delete, pagination, filter by speaker
- `SermonIsolationTest` — sermon invisible to other tenant, count isolated per tenant, API returns only own tenant's sermons

### Translation Files
- `lang/fr/sermons.php` — all field labels in French
- `lang/en/sermons.php` — English fallback

---

## Phase 2, Session 2 — Event Content Type

**Commit:** `(pending)` — `feat: Event content type — model, migration, Filament resource, API, tests`
**Date:** 2026-02-25

### Model
- **`App\Models\Event`** — BelongsToTenant, HasSoftVersioning, HasFactory
  - Casts: `start_at` (datetime), `end_at` (datetime), `capacity` (int), `custom_fields` (array), `previous_version` (array)
  - Hidden: `tenant_id`
  - Accessors: `is_upcoming` (start_at is future), `is_past` (end_at or start_at is past)

### Migration
| Migration | Table | Indexes |
|-----------|-------|---------|
| `2026_02_25_150000` | `events` | `tenant_id`, `(tenant_id, start_at)`, `(tenant_id, created_at)`, unique `(tenant_id, slug)`, GIN on `custom_fields` |

### Filament Resource
- **`App\Filament\Resources\EventResource`**
  - Navigation: icon `Heroicon::OutlinedCalendarDays`, group "Contenu", sort 2
  - Form: title (auto-slug), slug, start_at, end_at (validated after start_at), location, capacity, image URL, registration URL, description
  - Table: title, start_at, end_at, location, capacity; filters for upcoming/past; default sort by start_at desc
  - Pages: ListEvents, CreateEvent, EditEvent

### API Layer
| Endpoint | Controller Method | Auth |
|----------|------------------|------|
| `GET /api/v1/events` | `index` | Sanctum |
| `POST /api/v1/events` | `store` | Sanctum |
| `GET /api/v1/events/{event}` | `show` | Sanctum |
| `PUT /api/v1/events/{event}` | `update` | Sanctum |
| `DELETE /api/v1/events/{event}` | `destroy` | Sanctum |

- **Filtering:** `?location=` (ilike), `?upcoming=true`, `?past=true`
- **Pagination:** `?per_page=` (default 15)
- **Validation:** `end_at` must be after `start_at`; tenant-scoped unique slug

### Tests — 20 new (43 total, 136 assertions)

**Unit (6 tests)**
- `EventTest` — casts (custom_fields, start_at/end_at, capacity), hidden tenant_id, is_upcoming, is_past

**Feature (14 tests)**
- `EventApiTest` — 401 without auth, list, no tenant_id, create, show, update, delete, pagination, filter upcoming, filter location, end_at validation
- `EventIsolationTest` — event invisible to other tenant, count isolated, API scoped to tenant

---

## Upcoming: Phase 2, Session 3 — Announcement Content Type

Next session will build:
- `Announcement` model with BelongsToTenant, HasSoftVersioning
- Migration with `title`, `body`, `published_at`, `expires_at`, `pinned`, `target_group`, `custom_fields`, `previous_version`
- Filament resource with French labels
- API endpoints: `/api/v1/announcements`
- Tests: unit, API CRUD, tenant isolation
