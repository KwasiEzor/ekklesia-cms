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

### Page

| Column | Type | Notes |
|--------|------|-------|
| `title` | string | Required |
| `slug` | string | Unique per tenant |
| `content_blocks` | jsonb | Block-based content |
| `seo_title` | string | Nullable |
| `seo_description` | string | Nullable |
| `published_at` | datetime | Nullable |
| `custom_fields` | jsonb | GIN indexed |

---

### Giving Record

| Column | Type | Notes |
|--------|------|-------|
| `member_id` | foreignId | Nullable (anonymous) |
| `amount` | decimal(12,2) | Required |
| `currency` | string | XOF, EUR, USD, etc. |
| `date` | date | Required |
| `method` | string | mobile_money, cash, bank |
| `reference` | string | Transaction ID |
| `campaign_id` | foreignId | Nullable |
| `custom_fields` | jsonb | GIN indexed |

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
