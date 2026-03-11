# Content Type System

## Hybrid Strategy

Ekklesia uses a **hybrid content model**:

- **Known church content types** (Sermon, Event, Member, etc.) get proper relational columns — typed, indexed, and queryable with standard SQL
- **Administrator-defined custom fields** live in a `custom_fields JSONB` column with a GIN index — flexible, no schema migrations required

This avoids the EAV anti-pattern used by WordPress, which requires multiple JOINs for simple queries and degrades at scale. JSONB gives the same flexibility with far better performance.

## Core Content Types

### Sermon

| Column | Type | Notes |
|--------|------|-------|
| `title` | string | Required |
| `speaker` | string | Pastor name |
| `date` | date | Sermon date |
| `duration` | integer | Seconds |
| `audio_url` | string | Nullable |
| `video_url` | string | Nullable |
| `transcript` | text | Nullable, full-text indexed |
| `series_id` | foreignId | Nullable |
| `tags` | array (JSON) | Searchable |
| `custom_fields` | jsonb | GIN indexed |

**Example custom_fields:**
```json
{
  "scripture_reference": "Jean 3:16",
  "language": "fr",
  "translation_available": true,
  "outline_url": "https://..."
}
```

---

### Event

| Column | Type | Notes |
|--------|------|-------|
| `title` | string | Required |
| `start_at` | datetime | Required |
| `end_at` | datetime | Nullable |
| `location` | string | Physical or "En ligne" |
| `description` | text | Rich text |
| `image` | string | Via Spatie Media Library |
| `registration_url` | string | External or internal |
| `capacity` | integer | Nullable |
| `custom_fields` | jsonb | GIN indexed |

---

### Member

| Column | Type | Notes |
|--------|------|-------|
| `first_name` | string | Required |
| `last_name` | string | Required |
| `email` | string | Unique per tenant |
| `phone` | string | Mobile money linked |
| `baptism_date` | date | Nullable |
| `cell_group_id` | foreignId | Nullable |
| `status` | enum | active, inactive, visitor |
| `custom_fields` | jsonb | GIN indexed |

---

### Announcement

| Column | Type | Notes |
|--------|------|-------|
| `title` | string | Required |
| `body` | text | Rich text |
| `published_at` | datetime | Nullable — draft if null |
| `expires_at` | datetime | Auto-unpublish |
| `pinned` | boolean | Shown at top |
| `target_group` | string | all, members, leaders |
| `custom_fields` | jsonb | GIN indexed |

---

### Page <span class="decision-badge">IMPLEMENTED</span>

| Column | Type | Notes |
|--------|------|-------|
| `title` | string | Required |
| `slug` | string | Unique per tenant, auto-generated from title |
| `content_blocks` | jsonb | Block-based content (GIN indexed) |
| `seo_title` | string | Nullable — meta title for SEO |
| `seo_description` | string | Nullable — meta description for SEO |
| `published_at` | datetime | Nullable — draft if null, published if past |
| `custom_fields` | jsonb | GIN indexed |
| `previous_version` | jsonb | Soft versioning snapshot |

**Block types (Filament Builder component):**

| Block | Fields | Use case |
|-------|--------|----------|
| `heading` | level (h2/h3/h4), content | Section headings |
| `rich_text` | body (Markdown) | Main content paragraphs |
| `image` | url, alt, caption | Photos and illustrations |
| `video` | url, caption | YouTube/Vimeo embeds |
| `call_to_action` | label, url, style (primary/secondary) | Buttons and links |
| `quote` | text, attribution | Bible verses, testimonials |

**Computed attributes:**
- `is_published` — `true` when `published_at` is set and in the past

**API filters:**
- `?published=true` — only published pages
- `?search=` — title search (case-insensitive via `ilike`)

---

### Giving Record <span class="decision-badge">IMPLEMENTED</span>

| Column | Type | Notes |
|--------|------|-------|
| `member_id` | foreignId | Nullable (anonymous giving) |
| `amount` | decimal(12,2) | Required |
| `currency` | string(3) | Default `XOF` — supports XOF, XAF, EUR, USD, GBP, CAD |
| `date` | date | Required |
| `method` | string | `mobile_money`, `cash`, `bank_transfer`, `card` |
| `reference` | string | Nullable — transaction ID / receipt number |
| `campaign_id` | string | Nullable — campaign reference |
| `custom_fields` | jsonb | GIN indexed |
| `previous_version` | jsonb | Soft versioning snapshot |

**Relationships:**
- `belongsTo(Member)` — nullable for anonymous giving

**Computed attributes:**
- `is_anonymous` — `true` when `member_id` is null
- `formatted_amount` — formatted with currency (e.g. "50 000,00 XOF")

**API filters:**
- `?method=mobile_money` — filter by payment method
- `?currency=XOF` — filter by currency
- `?member_id=5` — filter by member
- `?anonymous=true` — only anonymous donations
- `?campaign_id=easter-2026` — filter by campaign
- `?from=2026-01-01&to=2026-03-31` — date range filter

**Indexes:** `(tenant_id, date)`, `(tenant_id, method)`, `(tenant_id, currency)`, `(tenant_id, member_id)`, `(tenant_id, campaign_id)`

---

## Production Readiness Content Types

The following content types were added during the Production Readiness Sprint to complete the church management feature set.

### Service Type & Attendance <span class="decision-badge">IMPLEMENTED</span>

| Model | Key Columns | Notes |
|-------|-------------|-------|
| `ServiceType` | name, description, day_of_week, start_time | Defines recurring service slots |
| `Attendance` | service_type_id, member_id, date, check_in_time, is_first_time | Per-member attendance tracking |

**Features:** QR code check-in, first-time visitor flagging, trend dashboards (week-over-week, per campus, per service).

---

### Household <span class="decision-badge">IMPLEMENTED</span>

| Column | Type | Notes |
|--------|------|-------|
| `name` | string | Family/household name |
| `head_of_household_id` | foreignId | References a Member |
| `address`, `city`, `phone` | string | Shared contact info |
| `custom_fields` | jsonb | GIN indexed |

**Member additions:** `household_id`, `family_role` (head, spouse, child, relative), `date_of_birth`, `wedding_anniversary`.

---

### Fund & Campaign <span class="decision-badge">IMPLEMENTED</span>

| Model | Key Columns | Notes |
|-------|-------------|-------|
| `Fund` | name, description, type (tithe/offering/building/missions/benevolence), is_active | Categorizes giving |
| `Campaign` | name, fund_id, goal_amount, currency, start_date, end_date, is_active | Time-bound fundraising |

**GivingRecord update:** `fund_id` foreign key links giving to specific funds. Real-time progress calculation for campaigns.

---

### Prayer Request & Commitment <span class="decision-badge">IMPLEMENTED</span>

| Model | Key Columns | Notes |
|-------|-------------|-------|
| `PrayerRequest` | member_id, title, content, type (prayer/praise), visibility (public/group/confidential), status, is_answered | Prayer wall entries |
| `PrayerCommitment` | prayer_request_id, member_id | "Je prie" commitment pivot |

**Features:** Prayer chain broadcast, answered prayer marking with optional testimony link, commitment counter.

---

### Devotional & Series <span class="decision-badge">IMPLEMENTED</span>

| Model | Key Columns | Notes |
|-------|-------------|-------|
| `DevotionalSeries` | title, slug, description, is_active | Groups themed devotionals |
| `Devotional` | series_id, title, verse_reference, content, prayer_point, application, published_at | Daily devotional content |

**Features:** Multi-channel delivery (in-app, SMS, email), AI-assisted draft generation, scheduling system.

---

### Testimony <span class="decision-badge">IMPLEMENTED</span>

| Column | Type | Notes |
|--------|------|-------|
| `member_id` | foreignId | Author (nullable for anonymous) |
| `title` | string | Testimony title |
| `content` | text | Full testimony text |
| `category` | string | healing, provision, deliverance, conversion, family_restoration |
| `status` | string | submitted, approved, rejected |
| `is_anonymous` | boolean | Hides member identity |
| `is_featured` | boolean | Highlighted testimonies |
| `reactions` | jsonb | { amen: 5, gloire_a_dieu: 3, alleluia: 2 } |

**Features:** Moderation workflow, audio recording support, culturally appropriate reactions.

---

### Reading Plan & Progress <span class="decision-badge">IMPLEMENTED</span>

| Model | Key Columns | Notes |
|-------|-------------|-------|
| `ReadingPlan` | title, slug, description, duration_days, grace_period_days, is_active | Plan definition |
| `ReadingPlanDay` | reading_plan_id, day_number, passage_reference, passage_text, reflection | Daily entries |
| `MemberReadingProgress` | member_id, reading_plan_id, current_streak, longest_streak, started_at, completed_at | Progress tracking |

**Features:** Streak counter with configurable grace period, cell group plan assignments.

---

### Bulk Message & Template <span class="decision-badge">IMPLEMENTED</span>

| Model | Key Columns | Notes |
|-------|-------------|-------|
| `BulkMessage` | title, body, channel (sms/email/whatsapp), target_type, status, scheduled_at | Bulk delivery |
| `MessageTemplate` | name, body, channel, placeholders | Reusable templates |

**Features:** Scheduled delivery, audience targeting (all, cell_group, campus, status), per-recipient dispatch tracking via `NotificationDispatch`.

---

### Adjustment <span class="decision-badge">IMPLEMENTED</span>

| Column | Type | Notes |
|--------|------|-------|
| `adjustable_type` | string | Polymorphic (GivingRecord, PaymentTransaction) |
| `adjustable_id` | bigint | Reference to original record |
| `type` | string | void, correction |
| `reason` | text | Required explanation |
| `adjusted_by` | foreignId | User who made the adjustment |

**Purpose:** Financial records are immutable — adjustments provide a safe audit trail for voids and corrections without modifying original records.

---

## JSONB Indexing Strategy

```sql
-- GIN index on all custom_fields columns
-- Covers general containment queries (@>)
CREATE INDEX sermons_custom_fields_gin
  ON sermons USING GIN (custom_fields);

-- Expression index for a frequently-queried key
-- Add these per-need, not upfront
CREATE INDEX sermons_language
  ON sermons ((custom_fields->>'language'));
```

## Querying Custom Fields

```php
// Find all French sermons
Sermon::whereJsonContains('custom_fields->language', 'fr')->get();

// Find sermons with translation available
Sermon::where('custom_fields->translation_available', true)->get();

// All indexes above are used automatically by PostgreSQL
```

## Adding Custom Fields (Church Administrators)

Church administrators define their own custom fields through the Filament admin panel. The field definition is stored in a `content_type_schemas` table (per tenant), and the Filament form is dynamically rendered from this schema. No migrations are needed — the value is simply added to the `custom_fields` JSONB object.
