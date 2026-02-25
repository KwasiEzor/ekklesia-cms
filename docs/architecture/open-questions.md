# Open Architectural Questions

These items are not yet decided. Each must be resolved **before work begins on the relevant system component**. When a decision is made, it moves to [Core Decisions](/architecture/decisions) and this page is updated.

---

## 1. Plugin Architecture Contract

**Status:** Open — must resolve before v1 alpha  
**Impact:** High — shapes extensibility boundaries for every community plugin

The contract governing what a community plugin can and cannot do needs deliberate design to prevent plugins from accidentally (or maliciously) breaking tenant isolation.

Questions to answer:
- Which Filament hooks are available to plugins? (Resources, Pages, Navigation items, Actions)
- Can plugins add new API endpoints, or only extend existing ones?
- Can plugins define new database tables? If so, must those tables include `tenant_id`?
- How are plugin settings stored — per tenant or global?
- What is the plugin distribution mechanism — Composer packages, a marketplace, or both?

---

## 2. Content Versioning

**Status:** ~~Open~~ **Resolved** — see [Core Decisions](/architecture/decisions#content-versioning)
**Impact:** High — affects database schema for all content tables

**Decision:** Option 2 — Soft versioning with `previous_version` JSONB column, implemented via a reusable `HasSoftVersioning` Eloquent trait. Full details in the [decisions page](/architecture/decisions#content-versioning).

---

## 3. AI Context Pipeline Design

**Status:** Open — must resolve before AI module build  
**Impact:** Medium — determines AI response quality for church users

How content is structured and passed to the Claude API needs careful design.

Questions to answer:
- How is the system prompt constructed per tenant? What context does it include?
- How is context window size managed for long conversations?
- How are multi-turn conversations persisted? (Database? Redis? Client-side only?)
- How is the streaming response handled in the Filament slide-over UI?
- How are AI usage limits enforced per tenant per plan tier?

---

## 4. Mobile Money Provider Strategy

**Status:** Open — resolve before premium tier launch  
**Impact:** Medium — first premium feature, shapes payment architecture

Francophone Africa has several dominant mobile money providers with different APIs:

| Provider | Coverage | API Quality |
|----------|----------|-------------|
| MTN Mobile Money | West + Central Africa | REST API available |
| Orange Money | West Africa | REST API, more complex |
| Wave | Senegal, Côte d'Ivoire | Modern REST API |
| Moov Money | Togo, Benin | Older API |

Questions to answer:
- Which provider do we integrate first?
- Do we build direct integrations or use an aggregator (like CinetPay or FedaPay)?
- How do we handle the fact that API quality and availability vary significantly by country?

---

## 5. Local Language Support

**Status:** Open — post v1 stable, community-driven  
**Impact:** Low for v1 — but worth designing for from the start

Francophone African churches often conduct services mixing French with local languages — Ewe (Togo/Ghana), Lingala (DRC/Congo), Fon (Benin), Baoulé (Côte d'Ivoire), Wolof (Senegal).

Questions to answer:
- Does the Filament admin UI need to support these languages, or just the public-facing API?
- How are community language contributions managed and quality-controlled?
- Can `spatie/laravel-translatable` be extended to support right-to-left languages if needed?

::: tip Contributing
If you speak one of these languages and want to contribute translations, open an issue on GitHub. Community language contributions are among the most valuable the project can receive.
:::
