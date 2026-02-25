# Why Ekklesia?

## The Market Gap

Francophone Africa is one of the fastest-growing regions for Christianity in the world. Churches in countries like Togo, DRC, Côte d'Ivoire, Benin, and Cameroon are large, active, and deeply community-oriented. Yet the software serving them is either non-existent or completely misaligned with their needs.

Existing tools fail in three ways:

**Language** — Most church management software is English-only. French is the administrative language of 29 African countries and the mother tongue of over 320 million people. A CMS that does not think in French is not serious about serving this community.

**Infrastructure** — Western SaaS tools are hosted in US and EU data centers. Latency and connectivity costs make them sluggish or expensive for African users. Ekklesia defaults to providers with African infrastructure proximity.

**Culture and workflow** — African churches, particularly Pentecostal and charismatic congregations, have distinct workflows: revival events, cell group structures, tithe via mobile money, prophetic announcements, multilingual services. No existing tool models these natively.

## Why Open Source

Open source is not just a licensing choice — it is a community strategy. A church CMS that belongs to the African developer and pastor community will be maintained, translated, extended, and trusted in ways a proprietary tool never could be.

The open core model means the engine is free forever. The premium hosting platform and modules fund continued development. This is the same model that sustains Ghost, Metabase, and GitLab.

## Why Laravel and Filament

Laravel is the dominant PHP framework in Francophone African developer communities. A CMS built on Laravel means local developers can extend, maintain, and contribute without learning a new paradigm. Filament v5 provides a production-grade admin panel experience that would take years to build from scratch.

## Why Now

The combination of Laravel Cloud's one-click deployment, Filament v5's mature plugin architecture, PostgreSQL's JSONB capabilities, and the Claude API's multilingual AI assistant capabilities creates a window where building this properly is achievable by a small team. That window is now.

::: tip
Read the [Architecture Overview](/architecture/overview) to see how these choices translate into concrete technical decisions.
:::
