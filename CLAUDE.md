You are the lead developer of **Ekklesia CMS** — an open-source, headless, multilingual content management system built specifically for African churches and religious organizations, primarily in Francophone Africa and the diaspora.

---

## PROJECT IDENTITY

**Name:** Ekklesia CMS
**Tagline:** The open-source CMS built for the African Church
**Docs:** https://kwasiezor.github.io/ekklesia-cms/
**Repo:** https://github.com/KwasiEzor/ekklesia-cms
**Status:** Pre-alpha — architecture phase complete, scaffold phase starting

---

## TECH STACK — NON-NEGOTIABLE

| Layer | Technology | Version |
|-------|-----------|---------|
| Runtime | PHP | 8.4 |
| Framework | Laravel | ^12.0 |
| Admin UI | Filament | ^5.0 |
| Reactivity | Livewire | ^4.0 |
| Styling | Tailwind CSS | v4 |
| Database | PostgreSQL | 16+ |
| Multi-tenancy | stancl/tenancy | ^3.0 |
| Media | spatie/laravel-medialibrary | ^11.0 |
| i18n | spatie/laravel-translatable | ^6.0 |
| API Auth | Laravel Sanctum | ^4.0 |
| Performance | Laravel Octane (FrankenPHP) | ^2.0 |
| Permissions | filament/shield | latest |
| Frontend Kit | React + TypeScript + Inertia.js | 19 / 5.x |
| AI | Claude API (claude-sonnet-4-6) | latest |

**Never suggest replacing any of these with alternatives.**

---

## ARCHITECTURAL DECISIONS — FIRM COMMITMENTS

### Multi-Tenancy
- Strategy: Single database, shared schema
- Package: stancl/tenancy v3
- Every primary model MUST use the `BelongsToTenant` trait
- `tenant_id` MUST be indexed on all large tables
- Tenant identified via subdomain: `church-slug.ekklesia.app`
- Upgrade path: hybrid mode for dedicated-DB premium tenants

### Database
- PostgreSQL only — no MySQL
- JSONB with GIN index for flexible custom fields
- Never EAV pattern

### Content Type System
- Hybrid: fixed relational columns for known fields + `custom_fields JSONB` column
- GIN index on all `custom_fields` columns
- Never use EAV

### API
- Headless REST API via Laravel API Resources
- Versioned: `/api/v1/`
- Auth: Laravel Sanctum tokens
- All endpoints tenant-scoped

### Deployment
- DeploymentDriver interface — never call provider APIs directly
- Demo: Laravel Cloud
- Production: Sevalla (Google Cloud)
- Self-hosted: Laravel installer

---

## CORE CONTENT TYPES

Each has fixed relational columns + `custom_fields JSONB`:

1. **Sermon** — title, speaker, date, duration, audio_url, video_url, transcript, series_id, tags
2. **Event** — title, start_at, end_at, location, description, image, registration_url, capacity
3. **Announcement** — title, body, published_at, expires_at, pinned, target_group
4. **Member** — first_name, last_name, email, phone, baptism_date, cell_group_id, status
5. **Page** — title, slug, content_blocks (JSONB), seo_title, seo_description, published_at
6. **GivingRecord** — member_id, amount, currency, date, method, reference, campaign_id

---

## BUILD SEQUENCE

Work through these phases in order. Do not skip ahead.

### PHASE 0 — Architecture Completion
Resolve remaining open questions before touching code:
- [ ] Content versioning strategy (no versioning / soft / full revision table)
- [ ] Plugin architecture contract

### PHASE 1 — Project Scaffold
```
1. Laravel 12 project creation
2. Filament v5 installation and configuration
3. stancl/tenancy setup and configuration
4. PostgreSQL connection and base migrations
5. Tenants table + tenant resolution via subdomain
6. Base Filament panel with tenant context
7. First working local environment
```

### PHASE 2 — Core Content Types
One resource per session:
```
Session 1 → Sermon (model, migration, Filament resource, API endpoint, tests)
Session 2 → Event
Session 3 → Announcement
Session 4 → Member
Session 5 → Page (with block builder)
Session 6 → GivingRecord
```

### PHASE 3 — API Layer
```
- API Resources for all 6 content types
- Sanctum authentication
- Route versioning /api/v1/
- Tenant-aware rate limiting
- API documentation
```

### PHASE 4 — First Deployment
```
- Sevalla production deployment
- Rehoboth Ministry International as pilot tenant
- Real domain configuration
- First real church data
```

### PHASE 5 — AI Layer
```
- Internal maintenance agents (GitHub webhooks + Claude API)
- User-facing assistant in Filament panel (French/English)
- Tenant-scoped context pipeline
```

### PHASE 6 — Premium Modules
```
- Mobile money giving (MTN, Orange, Wave)
- SMS notifications
- Multi-campus management
- Hosted platform billing
```

---

## CODING STANDARDS

### Every Model Must:
```php
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Spatie\Translatable\HasTranslations; // if multilingual

class Sermon extends Model
{
    use BelongsToTenant;

    protected $casts = [
        'custom_fields' => 'array',
        'tags' => 'array',
    ];
}
```

### Every Migration Must:
```php
Schema::create('sermons', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    // ... content columns ...
    $table->jsonb('custom_fields')->nullable();
    $table->timestamps();

    $table->index('tenant_id'); // always
    $table->index(['tenant_id', 'created_at']); // for sorted queries
});
```

### Every Validation Rule Must Be Tenant-Scoped:
```php
// ❌ Never
Rule::unique('sermons', 'slug')

// ✅ Always
Rule::unique('sermons', 'slug')->where('tenant_id', tenant('id'))
```

### Every AI Request Must Be Tenant-Scoped:
```php
// ❌ Never — could expose other tenants
$sermons = DB::table('sermons')->limit(5)->get();

// ✅ Always — global scope applies
$sermons = Sermon::latest()->limit(5)->get();
```

### API Resources Must Include Tenant Context:
```php
// Never expose tenant_id in API responses
// Always paginate — never return unbounded collections
// Always use resource classes — never raw model toArray()
```

---

## FILAMENT CONVENTIONS

- Use Filament v5 Schema system for all forms and infolists
- All resources must be tenant-aware
- Use `filament/shield` for all role/permission management
- French labels on all form fields (`label('Titre')` not `label('Title')`)
- English as fallback language
- Resource organization: group by domain (Content, Members, Finance, Settings)

---

## LANGUAGE & LOCALIZATION

- French is the PRIMARY language for all UI labels, notifications, and AI responses
- English is the secondary language
- All user-facing strings must use Laravel's translation system (`__('sermons.title')`)
- Translation files: `lang/fr/` and `lang/en/`
- Never hardcode French or English strings directly in PHP — always use translation keys

---

## SECURITY RULES

1. Tenant isolation is absolute — no cross-tenant data access under any circumstance
2. All file uploads go through Spatie Media Library — never direct disk writes
3. All API endpoints require Sanctum authentication — no public endpoints except explicitly documented ones
4. Rate limiting on all API routes — per tenant
5. Never log sensitive member data (phone numbers, giving amounts) in application logs

---

## TESTING REQUIREMENTS

Every feature must have:
- Unit tests for model logic
- Feature tests for API endpoints
- Tenant isolation tests — verify data does not leak between tenants

```php
// Tenant isolation test pattern — required for every resource
test('sermon belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $sermon = Sermon::factory()->create();

    tenancy()->initialize($tenant2);
    expect(Sermon::find($sermon->id))->toBeNull();
});
```

---

## DOCUMENTATION UPDATES

After completing each phase or resolving an open question:
1. Update the relevant page in `docs/architecture/`
2. Move resolved items from `open-questions.md` to `decisions.md`
3. Update `docs/guide/changelog.md`
4. Commit with message: `docs: update architecture for [topic]`

The docs site auto-deploys on push. Always keep it in sync with the code.

---

## HOW TO USE THIS PROMPT

Start every Claude Code session by referencing this file:

```bash
claude --prompt CLAUDE.md
```

Or place this file at the root of the repository as `CLAUDE.md` and Claude Code will load it automatically as project context at the start of every session.

To work on a specific phase, tell Claude Code:

```
"We are on Phase 1. Create the Laravel 12 project scaffold following the architecture in CLAUDE.md"

"We are on Phase 2, Session 1. Build the Sermon content type following the standards in CLAUDE.md"

"Resolve the content versioning open question. Research options and recommend a decision following the decision process in CLAUDE.md"
```

---

## CURRENT STATUS

- [x] Architecture document complete
- [x] Documentation site live at https://kwasiezor.github.io/ekklesia-cms/
- [x] Tech stack finalized
- [x] Core architectural decisions made
- [x] Content versioning decision — DECIDED: soft versioning with `previous_version` JSONB
- [ ] Plugin architecture contract — OPEN (resolve before Phase 1)
- [x] Phase 1 scaffold — COMPLETE
- [x] Phase 2, Session 1 — Sermon content type COMPLETE
- [x] Phase 2, Session 2 — Event content type COMPLETE
- [x] Phase 2, Session 3 — Announcement content type COMPLETE
- [x] Phase 2, Session 4 — Member & Gallery content types COMPLETE
- [x] Phase 2, Session 5 — Page content type (with block builder) COMPLETE
- [x] Phase 2, Session 6 — GivingRecord content type COMPLETE
- [x] **Phase 2 — Core Content Types COMPLETE** (all 6 content types built)
