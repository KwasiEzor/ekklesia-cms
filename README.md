# Ekklesia CMS — Documentation

This directory contains the VitePress documentation site for Ekklesia CMS.

## Local Development

```bash
npm install
npm run docs:dev
```

The site will be available at `http://localhost:5173`.

## Build

```bash
npm run docs:build
```

Output is in `docs/.vitepress/dist/`.

## Structure

```
docs/
├── .vitepress/
│   ├── config.mts          # Site configuration, nav, sidebar
│   └── theme/
│       ├── index.ts         # Theme entry point
│       └── custom.css       # Ekklesia brand styles
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

## Deployment

The site deploys automatically to GitHub Pages when changes are pushed to `main` that affect the `docs/` directory.

See `.github/workflows/deploy-docs.yml` for the full workflow.

## Contributing to the Docs

Architecture decisions are living documents. When a decision is made or changed:

1. Update the relevant page in `docs/architecture/`
2. Move resolved items from `open-questions.md` to `decisions.md`
3. Update `changelog.md` with a note
4. Submit a PR — the deploy workflow handles the rest

## License

Documentation: [CC BY 4.0](https://creativecommons.org/licenses/by/4.0/)  
CMS Code: [MIT](../LICENSE)
