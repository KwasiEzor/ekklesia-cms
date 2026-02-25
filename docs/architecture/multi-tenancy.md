# Multi-Tenancy Architecture

## Strategy

Single database, shared schema. Every church organization is a **Tenant** record in a central `tenants` table. All primary application tables carry a `tenant_id` foreign key. Eloquent global scopes enforce tenant isolation automatically on every query.

The `stancl/tenancy` v3 package handles:
- Tenant identification via subdomain (`rehoboth.ekklesia.app`)
- Bootstrapping tenant context (cache, filesystem, queue) on each request
- The `BelongsToTenant` trait that applies global scopes to all primary models

## Tenant Resolution Flow

```
Request: https://rehoboth.ekklesia.app/api/sermons
         │
         ▼
[TenancyMiddleware]
Extracts subdomain → "rehoboth"
Resolves Tenant model from DB
         │
         ▼
[TenancyBootstrapper]
Sets tenant in context
Configures scoped cache (redis prefix: "rehoboth:")
Configures scoped filesystem (storage/tenants/rehoboth/)
         │
         ▼
[BelongsToTenant Global Scope]
All Eloquent queries automatically add:
WHERE tenant_id = [rehoboth_id]
         │
         ▼
Response: only Rehoboth's sermons returned ✓
```

## Database Schema Pattern

Every primary model follows this pattern:

```php
// Migration
Schema::create('sermons', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->date('date');
    // ... other columns
    $table->jsonb('custom_fields')->nullable();
    $table->timestamps();

    $table->index('tenant_id'); // Always index tenant_id
});

// Model
class Sermon extends Model
{
    use BelongsToTenant; // Applies global scope automatically

    protected $casts = [
        'custom_fields' => 'array',
        'date' => 'date',
    ];
}
```

## Developer Rules

::: danger These rules are not optional
Violating any of these can cause data leakage between tenants.
:::

1. **Every primary model MUST use the `BelongsToTenant` trait**
2. **`tenant_id` MUST be indexed on all large tables**
3. **Validation `unique()` rules MUST be scoped to `tenant_id`**

```php
// ❌ WRONG — not scoped to tenant
Rule::unique('sermons', 'slug')

// ✅ CORRECT — scoped to current tenant
Rule::unique('sermons', 'slug')->where('tenant_id', tenant('id'))
```

4. **Cross-tenant queries are NEVER permitted** outside the central admin context
5. **Tenant context MUST be set before any queue job** processes tenant data

## Upgrade Path to Dedicated Databases

For large denominations or enterprise customers requiring full database isolation, `stancl/tenancy` supports a **hybrid mode** where specific tenants are routed to their own database while others remain in the shared database.

This is the premium tier path and requires no architectural changes — just a configuration update per tenant. The migration is handled by the platform admin, not the church.

```php
// Premium tenant gets dedicated DB
$tenant->update(['uses_dedicated_database' => true]);
// Platform provisions new DB and migrates tenant data
// Future requests for this tenant route to dedicated connection
```
