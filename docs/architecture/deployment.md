# Deployment Architecture

## The Deployment Driver Interface

All deployment actions are abstracted behind a `DeploymentDriver` contract. The CMS core never calls Laravel Cloud or Sevalla APIs directly. This means switching providers or adding self-hosted support never touches core business logic.

```php
interface DeploymentDriver
{
    public function provisionDemo(Tenant $tenant): DeploymentResult;

    public function provisionProduction(
        Tenant $tenant,
        string $domain
    ): DeploymentResult;

    public function destroy(string $deploymentId): void;

    public function getStatus(string $deploymentId): DeploymentStatus;
}
```

New providers implement this interface and are registered in the config:

```php
// config/ekklesia.php
'deployment' => [
    'driver' => env('DEPLOYMENT_DRIVER', 'sevalla'),
    'drivers' => [
        'laravel-cloud' => LaravelCloudDriver::class,
        'sevalla'       => SevallaDriver::class,
        'self-hosted'   => SelfHostedDriver::class,
    ],
],
```

---

## Provider Strategy

### Laravel Cloud — Demo Environments

Used for: sandboxed demo environments, developer testing, short-lived previews.

- Provisioning time: ~90 seconds
- Auto-expiry: 48 hours (configurable)
- Pre-populated with realistic demo church data
- Rate-limited: one demo per email address per 24 hours
- Abuse prevention: email verification required before provisioning

**Demo flow:**
```
User clicks "Try Demo" on ekklesia.app
  → Email verified
  → LaravelCloudDriver::provisionDemo() called
  → Container spun up with seed data
  → Subdomain assigned: demo-[hash].ekklesia.app
  → Login link emailed
  → Demo expires after 48h, container destroyed
```

---

### Sevalla — Production Deployments

Used for: real church production instances.

- Infrastructure: Google Cloud Platform
- Points of presence: EU and Africa (closer latency for African users)
- Custom domain support
- Managed PostgreSQL included
- Automated SSL via Let's Encrypt

**Production flow:**
```
Church subscribes on ekklesia.app
  → SevallaDriver::provisionProduction() called
  → Container provisioned on GCP
  → PostgreSQL instance created
  → Domain DNS configured
  → SSL certificate issued
  → Church admin receives credentials
```

---

### Self-Hosted — Technical Users

Used for: developers and technical churches who want full control on their own VPS or servers.

We provide a production-ready Docker architecture to run Ekklesia CMS natively anywhere using `docker-compose.prod.yml`.

**The Production Stack:**
- **FrankenPHP (App):** Laravel Octane server for high-performance requests.
- **PostgreSQL 16:** Primary database.
- **Redis 7:** Caching, queues, and session management.
- **Laravel Reverb:** Dedicated container for real-time WebSocket events (AI streaming, notification dashboards).
- **Worker & Scheduler:** Containers for asynchronous jobs and CRON.
- **Caddy:** Reverse proxy with automatic SSL (Let's Encrypt) routing traffic to Octane (`:8000`) and Reverb (`:8080`).

```bash
# Provisioning a production server
cp .env.example .env
# Edit .env with APP_DOMAIN, ACME_EMAIL, DB/Redis passwords
docker-compose -f docker-compose.prod.yml up -d --build
```

No hosting fees. No platform dependency. Full control. The open-source code is MIT-licensed.

---

## Environment Configuration

```bash
# .env — Deployment provider selection
DEPLOYMENT_DRIVER=sevalla

# Laravel Cloud
LARAVEL_CLOUD_API_KEY=
LARAVEL_CLOUD_PROJECT_ID=

# Sevalla
SEVALLA_API_KEY=
SEVALLA_REGION=europe-west1  # Closest to Francophone Africa

# Demo settings
DEMO_EXPIRY_HOURS=48
DEMO_MAX_PER_EMAIL_PER_DAY=1
```

---

## Infrastructure Notes

::: tip Why Sevalla for production?
Sevalla runs on Google Cloud Platform which has edge nodes in Johannesburg (South Africa), Lagos (Nigeria via partners), and multiple EU locations. For users in Togo, DRC, Côte d'Ivoire, and Cameroon — and the diaspora in Belgium and France — this reduces latency compared to US-only providers like Laravel Forge/DigitalOcean US.
:::
