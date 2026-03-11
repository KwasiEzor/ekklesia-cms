# Introduction

**Ekklesia CMS** is a headless, multilingual, open-source content management system purpose-built for churches and religious organizations — primarily in Francophone Africa and the African diaspora in Europe.

## The Problem

Churches in Togo, DRC, Côte d'Ivoire, Benin, Cameroon, and the francophone diaspora in Belgium and France are currently managing their online presence with tools that were never designed for them:

- **WordPress** — bloated, insecure without maintenance, culturally generic, English-first
- **Western church software** (ChurchSuite, Planning Center) — priced in USD/GBP, built for Western congregation structures, no mobile money support
- **WhatsApp groups and spreadsheets** — for everything else

Nobody has built the right tool for this context. Ekklesia is that tool.

## What Ekklesia Provides

A church administrator in Lomé or Kinshasa gets:

- A **clean French-first admin panel** powered by Filament v5
- **Sermon management** — audio, video, transcripts, series, scripture references
- **Event coordination** — calendar, registration, capacity management
- **Member directory** — cell groups, households, baptism records, pastoral notes
- **Attendance tracking** — service types, QR check-in, trend dashboards
- **Giving records** — funds, campaigns, multi-currency support (XOF, XAF, EUR, USD)
- **Prayer wall** — prayer requests, commitments, answered prayers
- **Daily devotionals** — series, scheduling, multi-channel delivery
- **Testimony sharing** — moderation workflow, culturally appropriate reactions
- **Bible reading plans** — streak tracking, progress monitoring
- **SMS bulk messaging** — scheduled delivery, audience targeting
- **Birthday & anniversary auto-notifications** — configurable per tenant
- **An AI assistant** that drafts announcements, summarizes sermons, and answers how-to questions in French
- **Multilingual content** — French, English, and local language support on the roadmap

A developer in the community gets:

- A **clean Laravel 12 + Filament v5** codebase to extend
- A **headless REST API** with 60+ endpoints to build any frontend against
- A **plugin architecture** following Filament's own conventions
- Full self-hosting capability with Docker and FrankenPHP

## What Ekklesia Is Not

Ekklesia is not a general-purpose CMS. It will never try to compete with WordPress at scale or replace Contentful for enterprise content teams. It is a **focused tool for a specific, underserved community** — and that focus is its strength.

## Project Status

Ekklesia has completed **Phases 0–5** and is actively building **Phase 6 — Premium Modules**. The core CMS, API layer, AI assistant, and 9 production-readiness features are fully implemented with **568 passing tests** (1561 assertions).

::: warning Status: Alpha
Ekklesia is in active development. The core architecture is stable, but the API contract and plugin system may still evolve before v1.0 stable.
:::

## Next Steps

- Read [Why Ekklesia?](/guide/why-ekklesia) for the full context and market reasoning
- Explore the [Architecture Overview](/architecture/overview) to understand the system design
- See the [Tech Stack](/architecture/stack) for the full dependency list
- Check the [Roadmap](/guide/roadmap) for what is coming and when
