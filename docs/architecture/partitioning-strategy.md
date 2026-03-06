# Database Partitioning Strategy

This document outlines the strategy for partitioning high-volume tables in Ekklesia CMS to ensure long-term performance and maintainability.

## Target Tables

1.  **`activity_log`**: Stores all audit trails, including financial logs. Expected to grow rapidly.
2.  **`media`**: Stores metadata for all uploads. Expected to grow moderately but can become a bottleneck for large multi-tenant installations.

## Partitioning Method: PostgreSQL Declarative Partitioning

We will use **Range Partitioning** based on the `created_at` column.

### 1. `activity_log` Partitioning

-   **Partition Key**: `created_at` (timestamp).
-   **Interval**: Monthly.
-   **Naming Convention**: `activity_log_yYYYY_mMM` (e.g., `activity_log_y2026_m03`).
-   **Retention Policy**: 
    -   Keep 12 months in the main database.
    -   Older partitions can be detached and archived to cold storage or compressed.

#### Implementation Steps:
1.  **Create Parent Table**: Define the `activity_log` table as a partitioned table.
2.  **Initial Partitions**: Create partitions for the current month and the next 3 months.
3.  **Automation**: Implement a scheduled task (Laravel Command) that runs monthly to create the next partition ahead of time.
4.  **Migration Path**:
    -   Rename `activity_log` to `activity_log_old`.
    -   Create partitioned `activity_log`.
    -   Migrate data from `activity_log_old` into `activity_log` (Postgres will automatically route rows to correct partitions).
    -   Drop `activity_log_old`.

### 2. `media` Partitioning

-   **Partition Key**: `created_at` (timestamp) or `id` (range).
-   **Preference**: Range partitioning by `id` or `created_at`. If using `created_at`, it aligns with `activity_log`.
-   **Interval**: Yearly (given lower expected volume compared to logs).

## Technical Considerations

### Primary Keys & Indexes
In PostgreSQL partitioned tables:
-   The partition key must be part of any unique index (including the Primary Key).
-   This means the PK for `activity_log` will become `(id, created_at)`.

### Foreign Keys
-   Foreign keys pointing TO a partitioned table are supported in PostgreSQL 12+.
-   Ekklesia CMS uses Laravel 12 and PostgreSQL 15+, so this is fully supported.

### Application Logic
-   No changes are required in Eloquent models.
-   PostgreSQL handles routing and partition pruning automatically.

## Roadmap

1.  [ ] Create migration for `activity_log` partitioning.
2.  [ ] Create migration for `media` partitioning.
3.  [ ] Implement `app:create-partitions` command.
4.  [ ] Schedule partition creation in `routes/console.php`.
