# Core Architectural Decisions

These decisions are **firm commitments**, not suggestions. They were reached after deliberate research and reasoning. Any future change requires explicit justification and an update to this document.

## Decision Log

### Multi-Tenancy Strategy <span class="decision-badge">DECIDED</span>

**Decision:** Single database with `tenant_id` column scoping, using `stancl/tenancy` v3.

**Rationale:** For a solo or small team, lower DevOps complexity is more valuable than maximum isolation. A single database with Eloquent global scopes handles tenant data separation cleanly and reliably. PostgreSQL's query optimizer handles tenant-scoped queries efficiently at the scale Ekklesia will operate.

The architecture explicitly supports a **hybrid upgrade path** — premium tenants can be migrated to dedicated databases without rebuilding the system. This is exposed as an enterprise tier feature.

::: tip Why not separate databases per tenant?
Separate-database tenancy offers stronger isolation but requires managing N database connections, N migration runs, and N backup strategies. For a small team building the first version, this overhead is unjustified. The single-database approach is how most successful Laravel SaaS products start.
:::

---

### Database Engine <span class="decision-badge">DECIDED</span>

**Decision:** PostgreSQL 16+.

**Rationale:** Two reasons made this choice non-negotiable. First, JSONB support — PostgreSQL's JSONB type with GIN indexing is the foundation of the content type flexibility strategy. MySQL's JSON support is inferior for the query patterns Ekklesia needs. Second, PostgreSQL's query planner handles complex tenant-scoped queries with indexed `tenant_id` columns significantly better than MySQL at scale.

---

### Content Type System <span class="decision-badge">DECIDED</span>

**Decision:** Hybrid model — fixed relational columns for known church content types, JSONB `custom_fields` column for administrator-defined custom fields.

**Rationale:** The EAV (Entity-Attribute-Value) pattern, used by WordPress for custom fields, is a known performance anti-pattern. It requires multiple JOINs for simple queries and degrades significantly at scale. JSON columns in PostgreSQL (JSONB) solve the same flexibility problem with a fraction of the query complexity and far better performance — benchmark results show JSONB is 50,000x faster than EAV for full-table scans without indexes, and still faster with indexes.

Core church content (Sermons, Events, Members) gets proper relational columns for type safety and performance. Custom fields defined by church administrators live in a `custom_fields JSONB` column with a GIN index.

---

### Frontend Delivery <span class="decision-badge">DECIDED</span>

**Decision:** Headless REST API with versioning.

**Rationale:** Decoupling the admin from the frontend means churches and developers choose their own frontend technology. A provided Blade/Inertia starter theme handles non-technical users who need a website out of the box. Developers building React Native apps, Next.js sites, or custom solutions consume the same REST API. This is the model that makes a CMS genuinely useful as a platform rather than a closed system.

---

### Tenancy Package <span class="decision-badge">DECIDED</span>

**Decision:** `stancl/tenancy` v3.

**Rationale:** The most feature-complete Laravel tenancy package. Supports both single-DB and multi-DB modes (enabling the hybrid upgrade path). Has excellent Filament compatibility. Active maintenance and large community.

---

### Deployment Providers <span class="decision-badge">DECIDED</span>

**Decision:** Laravel Cloud for demos, Sevalla for production — both wrapped behind a `DeploymentDriver` interface.

**Rationale:** Laravel Cloud offers the best developer experience for Laravel projects and the fastest demo provisioning. Sevalla (Google Cloud) has better infrastructure proximity for African users and is more appropriate for production workloads. The abstraction interface means neither provider is a hard dependency — adding a third provider or swapping one out never touches core business logic.

---

### API Authentication <span class="decision-badge">DECIDED</span>

**Decision:** Laravel Sanctum.

**Rationale:** Sanctum is first-party, lightweight, and handles both SPA cookie authentication and mobile/API token authentication cleanly. Laravel Passport adds OAuth server complexity that Ekklesia does not need in v1.

---

### Plugin Architecture <span class="decision-badge">OPEN</span>

**Status:** Not yet decided. Must be resolved before v1 alpha.

The contract governing what a community plugin can and cannot do — which data models it can access, which Filament hooks it can use, whether it can extend the API — needs deliberate design to prevent plugins from breaking tenant isolation.

---

### Content Versioning <span class="decision-badge">OPEN</span>

**Status:** Not yet decided. Must be resolved before v1 alpha.

Whether content types support revision history affects the database schema design for all content tables. If versioning is included, tables need a `revision_id` or a separate `_revisions` table pattern. The cost of adding this later is high.

---

### AI Context Pipeline <span class="decision-badge">OPEN</span>

**Status:** Not yet decided. Must be resolved before the AI module is built.

How content is structured and passed to the Claude API — which fields, in what format, with what context window management — determines the quality of AI responses for church users.
