# Quick Start

::: warning Pre-Alpha
Ekklesia is not yet available for installation. This page describes the planned quick start experience for v1.0 alpha. Content will be updated as development progresses.
:::

## Planned Installation (Self-Hosted)

```bash
# Requires PHP 8.4, PostgreSQL 16+, Node.js 20+

composer create-project ekklesia/cms my-church
cd my-church
cp .env.example .env

# Configure your database in .env
php artisan ekklesia:install
php artisan serve
```

The installer will guide you through:
- Database setup and migrations
- First tenant (your church) creation
- Admin user creation
- Optional demo content seeding

## Planned One-Click Deploy

For non-technical users, a deploy button will be available at [ekklesia.app](https://ekklesia.app):

1. Click **Deploy Demo** — a sandboxed environment is ready in ~90 seconds
2. Explore with pre-populated church content
3. When ready, click **Go Live** to provision a production instance on your own domain

## System Requirements (Planned)

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| PHP | 8.4 | 8.4 |
| PostgreSQL | 15 | 16+ |
| Node.js | 20 | 22 LTS |
| Memory | 512MB | 1GB+ |
| Storage | 5GB | 20GB+ |

## Stay Updated

Watch the [GitHub repository](https://github.com/kwasiEzor/ekklesia-cms) and check the [Roadmap](/guide/roadmap) for progress updates.
Online doc [GitHub Doc](https://kwasiezor.github.io/ekklesia-cms/)
