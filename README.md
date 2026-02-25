# Ekklesia CMS

An open-source, headless, multilingual content management system purpose-built for churches and religious organizations in Francophone Africa and the diaspora.

## The Problem

Churches in Togo, DRC, Cote d'Ivoire, Benin, Cameroon, and the francophone diaspora in Belgium and France manage their online presence with tools that were never designed for them — WordPress is bloated and English-first, Western church software is priced in USD/GBP with no mobile money support, and everything else runs on WhatsApp groups and spreadsheets.

Ekklesia is the tool that was missing.

## Features

- **French-first admin panel** powered by Filament v5
- **Sermon management** — audio, video, transcripts, series, scripture references
- **Event coordination** — calendar, registration, capacity management
- **Member directory** — cell groups, baptism records, pastoral notes
- **Giving records** — including mobile money (MTN, Orange, Wave)
- **Multilingual content** — French, English, and local language support
- **AI assistant** — drafts announcements, summarizes sermons, and answers how-to questions in French
- **Headless REST API** — build your frontend in Next.js, Nuxt, React Native, or plain Blade
- **Multi-tenant by design** — one installation, many churches, fully isolated data

## Tech Stack

- Laravel 12
- Filament v5
- VitePress (documentation)

## Project Status

Ekklesia is currently in **pre-alpha architecture phase**. Core architectural decisions have been made and documented. Active development begins once the architecture documentation is complete.

> **Warning:** Do not use in production. The API, database schema, and plugin contracts are all subject to change without notice until v1.0 stable.

## Documentation

The full documentation site is built with VitePress and lives in the `docs/` directory.

### Local Development

```bash
npm install
npm run docs:dev
```

The docs site will be available at `http://localhost:5173`.

### Build

```bash
npm run docs:build
```

### Docs Structure

```
docs/
├── index.md                 # Homepage
├── guide/
│   ├── introduction.md
│   ├── why-ekklesia.md
│   ├── quick-start.md
│   ├── roadmap.md
│   └── changelog.md
└── architecture/
    ├── overview.md
    ├── decisions.md
    ├── stack.md
    ├── multi-tenancy.md
    ├── content-types.md
    ├── ai.md
    ├── deployment.md
    ├── business-model.md
    └── open-questions.md
```

## Contributing

Architecture decisions are living documents. When a decision is made or changed:

1. Update the relevant page in `docs/architecture/`
2. Move resolved items from `open-questions.md` to `decisions.md`
3. Update `changelog.md` with a note
4. Submit a PR — the deploy workflow handles the rest

## License

Documentation: [CC BY 4.0](https://creativecommons.org/licenses/by/4.0/)
CMS Code: [MIT](LICENSE)
