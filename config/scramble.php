<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    'api_path' => 'api',

    'api_domain' => null,

    'export_path' => 'api.json',

    'info' => [
        'version' => env('API_VERSION', '1.0.0'),
        'description' => <<<'MD'
# Ekklesia CMS API

Headless REST API for African church content management.

## Authentication

All content endpoints require a Sanctum bearer token. Obtain one via `POST /api/v1/auth/login`.

```
Authorization: Bearer ekklesia_1|abc123...
```

## Tenant Scoping

All data is automatically scoped to the authenticated user's tenant. Cross-tenant access is not possible.

## Rate Limiting

- **API endpoints:** 60 requests/minute per tenant + IP
- **Auth endpoints:** 5 requests/minute per IP
MD,
    ],

    'ui' => [
        'title' => 'Ekklesia CMS API',
        'theme' => 'light',
        'hide_try_it' => false,
        'hide_schemas' => false,
        'logo' => '',
        'try_it_credentials_policy' => 'include',
        'layout' => 'responsive',
    ],

    'servers' => null,

    'enum_cases_description_strategy' => 'description',

    'enum_cases_names_strategy' => false,

    'flatten_deep_query_parameters' => true,

    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    'extensions' => [],
];
