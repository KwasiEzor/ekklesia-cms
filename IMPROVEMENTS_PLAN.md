# Ekklesia CMS — Strategic Improvement Plan

This plan outlines the final steps to move from a high-quality prototype to a production-ready, enterprise-grade CMS for churches.

## Stage 1: Hardening & Core Integrity (Week 1)
*Goal: Ensure data safety, privacy, and AI reliability.*

### 1.1 Financial Robustness
- **Custom Exceptions:** Replace raw `\Exception` in `GivingRecord` and `PaymentTransaction` with a specialized `ImmutableRecordException`.
- **UI Hardening:** Ensure all financial forms explicitly disable critical fields during any "view" or accidental "edit" states.
- **Validation:** Add server-side validation to prevent any "backdoor" updates to immutable fields via API.

### 1.2 AI Assistant Optimization (RAG Prep)
- **Advanced Skill Detection:** Implement keyword/regex mapping in `SkillRegistry` so the AI "senses" intent even without `/slug` commands.
- **Contextual Performance:** Cache `aggregateStats` in `TenantContextBuilder` to prevent N+1 queries on every AI message.
- **Granular Financial Context:** Provide the AI with "Safe Totals" (e.g., this month vs. last month) to enable meaningful trend analysis without exposing PII.

### 1.3 Security & Privacy Audit
- **PII Scrubbing:** Update `LogsActivityWithTenant` to automatically redact sensitive keys within `custom_fields` (e.g., "phone", "address", "amount").
- **Tenant Isolation Verification:** Add a suite of "Negative Tests" to ensure cross-tenant data leakage is impossible at the Eloquent level.

## Stage 2: Feature Gap Fill & Premium UX (Week 2)
*Goal: Complete the "daily driver" experience for church staff.*

### 2.1 Financial Correction Workflow
- **Adjustment System:** Since records are immutable, implement a "Void/Correction" system where a new record "cancels out" an old one with a clear audit trail.
- **Fund Management:** Complete the CRUD and logic for `Fund` and `Campaign` allocation.

### 2.2 Data Portability
- **Export/Import:** Build robust CSV/Excel export for Members and Giving Records (essential for church reports).
- **Media Optimization:** Ensure Spatie Media Library is configured for "Responsive Images" to save bandwidth in low-connectivity areas (common in some targeted regions).

### 2.3 Communication Hub
- **Notification History:** A dedicated dashboard for admins to see the delivery status of SMS/Emails sent via `BulkMessageJob`.
- **In-App Messaging:** Polish the real-time broadcasting notifications for better user feedback.

## Stage 3: Launch Prep & Operations (Week 3)
*Goal: Prepare for the "Real World".*

### 3.1 Observability
- **System Health:** Integrate `spatie/laravel-health` with checks for:
    - Database connection
    - Redis (Reverb/Cache)
    - Storage availability
    - AI API Quotas
- **Log Management:** Configure `laravel/pail` for production debugging.

### 3.2 Documentation & Training
- **Admin Runbook:** A simple guide for church treasurers and pastors.
- **API Reference:** Finalize Scramble docs for mobile app integration.

### 3.3 Final Load Test
- **Performance Benchmarking:** Simulate 100+ concurrent tenants to ensure Octane/FrankenPHP memory usage remains stable.

---
**Status:** ✅ Stage 1 & 2 complete. Stage 3 near completion.
