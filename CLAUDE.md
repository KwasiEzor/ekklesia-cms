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

For Codex + Claude collaboration mode, load these companion instructions too:
- `AI_COLLABORATION_PLAN.md`
- `CLAUDE_COLLAB_INSTRUCTIONS.md`

Suggested session start command:

```bash
claude --prompt CLAUDE.md --append AI_COLLABORATION_PLAN.md --append CLAUDE_COLLAB_INSTRUCTIONS.md
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
- [x] Phase 2.5 — Hardening Sprint COMPLETE (Rector, PHPStan, CI, security headers, rate limiting, Sanctum, SECURITY.md, CONTRIBUTING.md)
- [x] Phase 3 — API Layer COMPLETE (auth endpoints, token management, Scramble API docs, Gallery form requests)
- [x] Phase 4 — First Deployment COMPLETE (tenancy middleware, tenant:create, seeders, health check, Docker)
- [x] UI Refonte — Premium admin redesign COMPLETE (dashboard widgets, settings page, resource form redesign, translations, icon buttons, full-width forms)
- [x] Phase 5 — AI Layer COMPLETE (multi-provider assistant, 14 skills, tenant-scoped context pipeline, streaming via Reverb)
- [ ] Phase 6 — Premium Modules IN PROGRESS

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.16
- filament/filament (FILAMENT) - v5
- laravel/cashier (CASHIER) - v16
- laravel/framework (LARAVEL) - v12
- laravel/octane (OCTANE) - v2
- laravel/prompts (PROMPTS) - v0
- laravel/reverb (REVERB) - v1
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- rector/rector (RECTOR) - v2
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `pest-testing` — Tests applications using the Pest 3 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, architecture testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd and will be available at: `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs for the user.
- You must not run any commands to make the site available via HTTP(S). It is always available through Laravel Herd.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

</laravel-boost-guidelines>
