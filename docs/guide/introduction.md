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
- **Member directory** — cell groups, baptism records, pastoral notes
- **Giving records** — including mobile money (MTN, Orange, Wave)
- **Multilingual content** — French, English, and local language support on the roadmap
- **An AI assistant** that drafts announcements, summarizes sermons, and answers how-to questions in French

A developer in the community gets:

- A **clean Laravel 12 + Filament v5** codebase to extend
- A **headless REST API** to build any frontend against
- A **plugin architecture** following Filament's own conventions
- Full self-hosting capability with a simple installer

## What Ekklesia Is Not

Ekklesia is not a general-purpose CMS. It will never try to compete with WordPress at scale or replace Contentful for enterprise content teams. It is a **focused tool for a specific, underserved community** — and that focus is its strength.

## Project Status

Ekklesia is currently in **pre-alpha architecture phase**. The core architectural decisions have been made and documented. Active development begins once the architecture documentation is complete.

::: warning Status: Pre-Alpha
Do not use in production. The API, database schema, and plugin contracts are all subject to change without notice until v1.0 stable.
:::

## Next Steps

- Read [Why Ekklesia?](/guide/why-ekklesia) for the full context and market reasoning
- Explore the [Architecture Overview](/architecture/overview) to understand the system design
- See the [Tech Stack](/architecture/stack) for the full dependency list
- Check the [Roadmap](/guide/roadmap) for what is coming and when
