# Contributing to Ekklesia CMS

Thank you for your interest in contributing to Ekklesia CMS! This guide will help you get started.

## Development Setup

### Prerequisites

- PHP 8.4+
- PostgreSQL 16+
- Composer
- Node.js 22+

### Installation

```bash
git clone https://github.com/KwasiEzor/ekklesia-cms.git
cd ekklesia-cms
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build
```

## Code Quality Tools

All code must pass these checks before merging:

```bash
# Code style (Laravel Pint)
vendor/bin/pint --test

# Static analysis (PHPStan level 5 + Larastan)
vendor/bin/phpstan analyse --memory-limit=512M

# Automated refactoring check (Rector)
vendor/bin/rector process --dry-run

# Tests (Pest PHP)
php artisan test
```

## Coding Standards

### Models

Every model that stores tenant data must use `BelongsToTenant`:

```php
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class MyModel extends Model
{
    use BelongsToTenant;
}
```

### Migrations

Every table with tenant data must include:

```php
$table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
$table->index('tenant_id');
$table->index(['tenant_id', 'created_at']);
```

### Validation

Uniqueness rules must be tenant-scoped:

```php
Rule::unique('table', 'column')->where('tenant_id', tenant('id'))
```

### API Resources

- Never expose `tenant_id` in API responses
- Always paginate — never return unbounded collections
- Always use resource classes — never raw `toArray()`

### Localization

- French is the primary language for all UI labels
- All user-facing strings use translation keys: `__('resource.field')`
- Translation files live in `lang/fr/` and `lang/en/`
- Never hardcode French or English strings in PHP files

## Testing Requirements

Every new feature must include:

- **Unit tests** for model logic and accessors
- **Feature tests** for API endpoints
- **Tenant isolation tests** — verify data does not leak between tenants

```php
test('resource is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $record = MyModel::factory()->create();

    tenancy()->initialize($tenant2);
    expect(MyModel::find($record->id))->toBeNull();
});
```

## Pull Request Checklist

Before submitting a PR, verify:

- [ ] `vendor/bin/pint --test` passes
- [ ] `vendor/bin/phpstan analyse --memory-limit=512M` passes
- [ ] `vendor/bin/rector process --dry-run` shows no changes
- [ ] `php artisan test` — all tests pass
- [ ] New models use `BelongsToTenant` trait
- [ ] `tenant_id` is not exposed in API responses
- [ ] Form labels are French-first using translation keys
- [ ] Translation files updated in both `lang/fr/` and `lang/en/`
- [ ] Tenant isolation test included for new content types

## Commit Messages

Use conventional commit prefixes:

- `feat:` — new feature
- `fix:` — bug fix
- `docs:` — documentation only
- `chore:` — tooling, dependencies, config
- `ci:` — CI/CD changes
- `test:` — test additions or fixes
- `refactor:` — code changes that don't add features or fix bugs

## Branch Naming

- `feature/description` — new features
- `fix/description` — bug fixes
- `docs/description` — documentation changes

## Questions?

Open a [GitHub Discussion](https://github.com/KwasiEzor/ekklesia-cms/discussions) or reach out to the maintainers.
