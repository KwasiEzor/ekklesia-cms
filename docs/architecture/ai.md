# AI Architecture

Ekklesia has two distinct AI layers built on the same Claude API foundation. They are kept separate by design — different triggers, different contexts, different purposes.

## Layer 1 — Internal Maintenance Agents

These agents reduce the maintenance burden on the core team. They run in the background, triggered by GitHub webhooks, scheduled Artisan commands, or queue jobs.

::: tip All agent actions are proposed, not automatic
Agents draft responses and queue actions for human approval. No code is merged, no comment is posted, and no dependency is updated without a maintainer reviewing the agent's output first.
:::

### Agent Roster

| Agent | Trigger | Output |
|-------|---------|--------|
| **Issue Triage Agent** | GitHub: new issue opened | Labels applied, duplicates flagged, missing info requested |
| **PR Review Agent** | GitHub: PR opened | Code style feedback, tenant scope check, security flags |
| **Documentation Agent** | Git: commit pushed | Outdated doc flags, changelog draft entries |
| **Dependency Agent** | Dependabot: PR opened | Breaking change summary, merge safety assessment |
| **Support Agent** | GitHub Discussions: new post | Draft response for maintainer approval |

### Implementation Pattern

```php
// Each agent is a Laravel Job
class TriageIssueJob implements ShouldQueue
{
    public function __construct(
        private readonly GitHubIssue $issue
    ) {}

    public function handle(ClaudeAgentService $claude): void
    {
        $response = $claude->triage($this->issue);

        // Propose action — never execute directly
        AgentProposal::create([
            'type'    => 'issue_triage',
            'payload' => $response,
            'status'  => 'pending_review',
        ]);
    }
}
```

---

## Layer 2 — User-Facing Assistant

Embedded in the Filament admin panel as a slide-over panel. Available to all church administrators. Communicates in **French by default**, switches to English on request.

### Context Awareness

The assistant is fully context-aware. When a church administrator opens the assistant panel, the Claude API receives:

- The current tenant's name and profile
- The current page/resource being viewed (e.g., "editing a Sermon")
- The tenant's configured primary language
- A summary of recently created content (for continuity)

This means asking _"Can you draft an announcement for this event?"_ works — the assistant already knows which event is open.

### Capabilities

- **Content drafting** — Announcement text, newsletter paragraphs, event descriptions in French or English
- **Sermon summarization** — Convert a sermon transcript into a 3-paragraph newsletter summary
- **Translation assistance** — Translate content between French and English
- **SEO suggestions** — Generate meta titles and descriptions for church pages
- **How-to guidance** — Answer questions about using the CMS (_"Comment ajouter un nouveau membre?"_)

### Implementation

```php
// AssistantController — receives messages from Filament slide-over
public function chat(AssistantRequest $request): StreamedResponse
{
    // Context is always tenant-scoped
    $context = $this->buildTenantContext(
        tenant: currentTenant(),
        page: $request->current_page,
        language: $request->language ?? 'fr'
    );

    return $this->claudeService->streamChat(
        messages: $request->messages,
        systemPrompt: $context->toSystemPrompt(),
    );
}
```

---

## Critical: Tenant Isolation in AI

::: danger AI requests are tenant-scoped — no exceptions
All content fed to the Claude API must come from the current tenant's data only. The context pipeline never constructs prompts from raw DB queries. It always goes through tenant-scoped Eloquent models. Cross-tenant data in AI context is treated as a security incident.
:::

```php
// ❌ WRONG — could expose other tenants' data
$sermons = DB::table('sermons')->limit(5)->get();

// ✅ CORRECT — scoped to current tenant via global scope
$sermons = Sermon::latest()->limit(5)->get();
```

---

## Open: AI Context Pipeline Design

The detailed design of the context pipeline — how content is serialized, how context window size is managed, how multi-turn conversations maintain state — is an [open architectural question](/architecture/open-questions) to be resolved before building the AI module.
