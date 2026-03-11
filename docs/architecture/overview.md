# Architecture Overview

Ekklesia follows a **four-layer architecture**. Each layer is loosely coupled by design, allowing each to evolve independently.

## The Four Layers

### Layer 1 — Admin Panel

Filament v5 on Laravel 12. The complete back-office for church administrators.

Manages: content types, members, households, attendance, giving, prayer requests, devotionals, testimonies, reading plans, bulk messaging, roles, and tenant settings. Multi-tenant from day one — a single installation serves many churches, each fully isolated.

### Layer 2 — API Delivery

A headless REST API powered by Laravel API Resources. Any frontend can consume it:
- The provided Blade/Inertia starter theme
- A custom Next.js or Nuxt frontend
- A React Native mobile app
- Third-party integrations

The API is versioned, authenticated with Laravel Sanctum, and designed to be cacheable at every endpoint.

### Layer 3 — AI Intelligence

A multi-provider AI system with 14 specialized skills:

**Multi-provider support** — Claude, OpenAI, and Gemini via a driver-based `AiManager` pattern. Each tenant can configure their preferred provider.

**14 specialized skills** in 5 categories — Content Creation (sermon outlines, content writing, translation, SEO, proofreading), Church Management (event planning, communication drafts, giving insights, dashboard narration), Design & Branding, Security & Maintenance, and AI Guidance.

**User-facing assistant** — embedded in the Filament admin panel with streaming responses via Laravel Reverb. Helps church staff draft content, summarize sermons, translate text, and answer how-to questions in French or English. Context-aware with tenant-scoped data pipeline and PII filtering.

### Layer 4 — Deployment Abstraction

A provider-agnostic `DeploymentDriver` interface. One-click demo environments via Laravel Cloud. Production deployments via Sevalla (Google Cloud, African infrastructure proximity). Self-hosting via a simple installer script.

Switching providers never touches core business logic.

## System Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     CHURCH ADMINISTRATOR                     │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│              LAYER 1: ADMIN PANEL (Filament v5)             │
│  Sermons · Events · Members · Giving · Prayer · AI Chat     │
│  Attendance · Households · Devotionals · Testimonies · SMS   │
└──────────────────────────┬──────────────────────────────────┘
                           │ writes
┌──────────────────────────▼──────────────────────────────────┐
│            CORE: Laravel 12 + PostgreSQL                    │
│      Multi-tenant (stancl/tenancy) · JSONB Content          │
└──────┬───────────────────────────────────────┬──────────────┘
       │ serves                                │ feeds
┌──────▼──────────────┐             ┌──────────▼──────────────┐
│  LAYER 2: REST API  │             │  LAYER 3: AI LAYER      │
│  Laravel Sanctum    │             │  Claude API (Internal)  │
│  Versioned · Cached │             │  Claude API (User-facing)│
└──────┬──────────────┘             └─────────────────────────┘
       │ consumed by
┌──────▼──────────────────────────────────────────────────────┐
│              FRONTEND (any stack)                           │
│  Blade/Inertia starter · Next.js · Nuxt · React Native      │
└─────────────────────────────────────────────────────────────┘
```

## Key Design Principles

**Tenant isolation is non-negotiable.** Every query, every AI request, every file operation is scoped to the current tenant. There is no path through the system that allows cross-tenant data access.

**The core is stable; plugins extend it.** Core data models and the API contract are the foundation. All optional functionality — including premium features — is delivered via the plugin architecture without touching the core.

**Decisions are documented.** Every significant architectural choice has a written rationale in [Core Decisions](/architecture/decisions). This document is the source of truth and is updated whenever a decision changes.

## Next: Core Decisions

Read [Core Decisions](/architecture/decisions) for the reasoning behind each major technical choice.
