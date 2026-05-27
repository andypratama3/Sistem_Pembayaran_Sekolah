# Database Anomaly Fixes - Migration Summary

## Created Migrations (2026_05_07)

### 1. Anomaly #1 - students.alamat duplicate with students.address
**Migration:** `2026_05_07_000001_drop_alamat_from_students_table.php`
- Drops the `alamat` column from `students` table
- Keeps `address` column (English name, more standard)
- Rollback: Re-adds `alamat` column

### 2. Anomaly #2 - audit_logs dual tracking columns
**Migration:** `2026_05_07_000002_consolidate_audit_logs_columns.php`
- Removes legacy columns: `model` and `record_id`
- Keeps new columns: `model_type` and `model_id`
- Drops index on legacy columns
- Rollback: Re-adds legacy columns with index
- Note: Both column pairs were being written to (see HasAuditLog trait)

### 3. Anomaly #3 - roles.id and permissions.id use INT instead of UUID
**Migration:** `2026_05_07_000005_document_anomaly_3_roles_permissions_uuid.php` (Documentation only)
- **FINDING: NOT AN ANOMALY - Already using UUID!**
- Evidence in `2026_02_23_014431_create_permission_tables.php`:
  - Line 25: `$table->uuid('id')->primary(); // permission id`
  - Line 35: `$table->uuid('id')->primary(); // role id`
- No migration needed

### 4. Anomaly #4 - charges vs charges_archive structure difference
**Migration:** `2026_05_07_000003_add_missing_columns_to_charges_archive.php`
- Adds missing columns to `charges_archive`:
  - `order_id_alt` (nullable string)
  - `payment_title_id` (foreign key to payment_titles, nullable)
  - `action_name` (nullable string)
  - `method` (nullable string, 50 chars)
  - `action_url` (nullable text)
- Makes `charges_archive` structure match `charges` table
- Rollback: Drops added columns and foreign key

### 5. Anomaly #5 - achievement_categories confusion
**Migration:** `2026_05_07_000004_drop_unused_achievement_categories_table.php`
- Drops `achievement_categories` table
- Evidence of non-use:
  - No AchievementCategory model found
  - No usage found in app/ directory
  - Confirmed in task-implementation.md: "achievement_categories not found in models — possible unused table"
- Note: `achievement_category_labels` table is kept (has seeder)
- Rollback: Recreates the pivot table

### 6. Anomaly #6 - document_templates vs templates overlap
**Migration:** `2026_05_07_000006_document_anomaly_6_template_tables.php` (Documentation only)
- **FINDING: Different systems, not an anomaly**
- `document_templates`: Document generation system (admissions/documents)
- `templates`: Generic canvas-based template system with field definitions
- No consolidation needed - they serve different purposes
- Note: No DocumentTemplate model found (may need one if direct usage required)

## Usage

Run migrations:
```bash
php artisan migrate
```

Rollback specific migration:
```bash
php artisan migrate:rollback --step=N
```

## Checklist Before Running

- [ ] Backup database before running migrations
- [ ] Test in development environment first
- [ ] Verify `students.alamat` data is not needed (should be duplicate of `address`)
- [ ] Verify `audit_logs` legacy columns (`model`, `record_id`) have no special data
- [ ] Confirm `achievement_categories` table is truly unused
- [ ] Confirm `charges_archive` needs the added columns
