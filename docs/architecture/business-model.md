# Business Model

Ekklesia uses an **open core model**: the CMS engine is fully open source and free forever. The hosted platform and premium modules are the commercial layer that funds continued development.

This is the same model used successfully by Ghost, Metabase, and GitLab.

---

## Open Source Core (MIT License — Free Forever)

Everything a church needs to get online:

- Full CMS engine — all six core content types
- Filament admin panel
- Headless REST API
- Multi-tenancy (shared database)
- Multilingual content (French + English)
- Spatie Media Library integration
- Basic role and permission management
- Self-hosted deployment via installer
- Community plugin support
- Basic AI assistant (usage-limited)

**This will never move behind a paywall.**

---

## Premium Modules (Hosted Platform)

Available to churches on the hosted platform (`ekklesia.app`):

| Module | Description |
|--------|-------------|
| **AI Assistant (Unlimited)** | Unrestricted Claude API calls for content drafting, summarization, translation |
| **Mobile Money Giving** | MTN Mobile Money, Orange Money, Wave integration |
| **SMS Notifications** | Pastoral announcements via SMS (Twilio + African carriers) |
| **Multi-Campus** | Manage multiple church locations under one account |
| **Mobile App Builder** | React Native app generation for church members |
| **Dedicated DB Isolation** | Enterprise: dedicated PostgreSQL per church |
| **Priority Support** | Direct onboarding and technical assistance |

---

## Hosted Platform — ekklesia.app

The hosted platform is the primary revenue vehicle:

```
Free tier     → Demo only (expires in 48h)
Starter       → 1 church, shared DB, basic AI
Growth        → 1 church, shared DB, full AI + mobile money
Professional  → Multi-campus, unlimited AI, SMS, mobile app
Enterprise    → Dedicated DB, SLA, custom onboarding
```

All tiers above Free include custom domain support and Sevalla infrastructure.

---

## Why This Model Works for This Market

Churches in Francophone Africa are cost-conscious but will pay for tools that directly serve their mission. Mobile money integration and SMS notifications — things that directly affect giving and congregation communication — are natural premium features with clear value justification.

The open source core builds trust and community adoption. The hosted platform converts that trust into sustainable revenue. A church that starts on the free self-hosted version and grows will naturally migrate to the hosted platform as complexity increases.

---

## Revenue vs. Community

::: tip The balance
Premium features are things that have real API costs (AI, SMS) or real operational complexity (multi-campus, dedicated DB). Basic CMS functionality — creating content, managing members, publishing pages — is free because it should be. A church with 50 members in Lomé deserves the same CMS quality as one with 5,000.
:::
