# Database Schema Documentation

## Overview

Zagros CMMS uses PostgreSQL 16 with a normalized relational schema designed for:
- Asset lifecycle management
- Work order tracking
- Preventive maintenance scheduling
- Inventory management
- Analytics and reporting

## Core Domains

### 1. User Management
### 2. Asset Management
### 3. Work Orders
### 4. Maintenance
### 5. Inventory
### 6. Analytics

---

## 1. User Management

### `users`
User accounts and authentication information.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| email | VARCHAR(180) | UNIQUE, NOT NULL | User email (login) |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| first_name | VARCHAR(100) | NOT NULL | First name |
| last_name | VARCHAR(100) | NOT NULL | Last name |
| phone | VARCHAR(20) | NULL | Contact phone |
| is_active | BOOLEAN | DEFAULT true | Account status |
| created_at | TIMESTAMP | NOT NULL | Creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update |

### `roles`
System roles for RBAC.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| name | VARCHAR(50) | UNIQUE, NOT NULL | Role name (ROLE_ADMIN) |
| description | TEXT | NULL | Role description |

**Predefined Roles:**
- `ROLE_ADMIN`: Full system access
- `ROLE_MANAGER`: Departmental management
- `ROLE_TECHNICIAN`: Work order execution
- `ROLE_VIEWER`: Read-only access

### `user_roles`
Many-to-many relationship between users and roles.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| user_id | UUID | FK users(id) | User reference |
| role_id | UUID | FK roles(id) | Role reference |

**Primary Key:** (user_id, role_id)

---

## 2. Asset Management

### `asset_categories`
Hierarchical categorization of assets.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| name | VARCHAR(100) | NOT NULL | Category name |
| parent_id | UUID | FK asset_categories(id) NULL | Parent category |
| description | TEXT | NULL | Category description |
| icon | VARCHAR(50) | NULL | Icon identifier |

**Examples:** HVAC, Electrical, Mechanical, Vehicles

### `locations`
Physical locations with hierarchical structure.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| name | VARCHAR(100) | NOT NULL | Location name |
| parent_id | UUID | FK locations(id) NULL | Parent location |
| address | TEXT | NULL | Physical address |
| coordinates | POINT | NULL | GPS coordinates |
| type | VARCHAR(50) | NULL | building, floor, room |

**Example Hierarchy:** Factory → Building A → Floor 2 → Room 201

### `assets`
Core asset registry.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| code | VARCHAR(50) | UNIQUE, NOT NULL | Asset code (AST-001) |
| name | VARCHAR(200) | NOT NULL | Asset name |
| category_id | UUID | FK asset_categories(id) | Category reference |
| location_id | UUID | FK locations(id) | Location reference |
| serial_number | VARCHAR(100) | NULL | Manufacturer serial |
| manufacturer | VARCHAR(100) | NULL | Manufacturer name |
| model | VARCHAR(100) | NULL | Model number |
| purchase_date | DATE | NULL | Purchase date |
| purchase_cost | DECIMAL(12,2) | NULL | Purchase cost |
| warranty_expiry | DATE | NULL | Warranty end date |
| status | VARCHAR(20) | NOT NULL | operational, down, maintenance |
| criticality_level | INTEGER | 1-5 | 1=Low, 5=Critical |
| qr_code | TEXT | NULL | QR code data |
| metadata | JSONB | NULL | Custom fields |
| created_at | TIMESTAMP | NOT NULL | Creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update |

**Indexes:**
- `idx_assets_code` on (code)
- `idx_assets_category` on (category_id)
- `idx_assets_location` on (location_id)
- `idx_assets_status` on (status)
- `idx_assets_metadata` GIN on (metadata)

### `asset_hierarchy`
Parent-child relationships between assets.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| parent_id | UUID | FK assets(id) | Parent asset |
| child_id | UUID | FK assets(id) | Child asset |
| relationship_type | VARCHAR(50) | NULL | contains, connects_to |

**Primary Key:** (parent_id, child_id)

**Example:** Conveyor System (parent) → Motor (child)

### `asset_meters`
Tracking counters for assets (hours, cycles, distance).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| asset_id | UUID | FK assets(id) | Asset reference |
| meter_type | VARCHAR(50) | NOT NULL | hours, cycles, kilometers |
| current_value | DECIMAL(12,2) | NOT NULL | Current reading |
| unit | VARCHAR(20) | NOT NULL | hours, km, cycles |
| last_reading_date | TIMESTAMP | NOT NULL | Last update |

### `asset_documents`
Manuals, diagrams, and related files.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| asset_id | UUID | FK assets(id) | Asset reference |
| title | VARCHAR(200) | NOT NULL | Document title |
| file_path | TEXT | NOT NULL | Storage path |
| file_type | VARCHAR(50) | NULL | pdf, image, cad |
| uploaded_by | UUID | FK users(id) | Uploader |
| uploaded_at | TIMESTAMP | NOT NULL | Upload time |

### `asset_history`
Audit trail for asset changes.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| asset_id | UUID | FK assets(id) | Asset reference |
| action | VARCHAR(50) | NOT NULL | created, updated, moved |
| field_changed | VARCHAR(100) | NULL | Field name |
| old_value | TEXT | NULL | Previous value |
| new_value | TEXT | NULL | New value |
| changed_by | UUID | FK users(id) | User who changed |
| changed_at | TIMESTAMP | NOT NULL | Change time |

---

## 3. Work Order Management

### `work_orders`
Work order tracking.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| number | VARCHAR(50) | UNIQUE, NOT NULL | WO-2026-001 |
| title | VARCHAR(200) | NOT NULL | Work order title |
| description | TEXT | NULL | Detailed description |
| asset_id | UUID | FK assets(id) NULL | Related asset |
| type | VARCHAR(50) | NOT NULL | corrective, preventive, predictive |
| priority | INTEGER | 1-5, NOT NULL | 1=Low, 5=Critical |
| status | VARCHAR(50) | NOT NULL | pending, assigned, in_progress, on_hold, completed, cancelled |
| assigned_to | UUID | FK users(id) NULL | Assigned technician |
| created_by | UUID | FK users(id) | Creator |
| due_date | TIMESTAMP | NULL | Deadline |
| scheduled_start | TIMESTAMP | NULL | Planned start |
| actual_start | TIMESTAMP | NULL | Actual start |
| actual_end | TIMESTAMP | NULL | Actual end |
| estimated_hours | DECIMAL(6,2) | NULL | Estimated duration |
| actual_hours | DECIMAL(6,2) | NULL | Actual duration |
| created_at | TIMESTAMP | NOT NULL | Creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update |
| completed_at | TIMESTAMP | NULL | Completion time |

**Indexes:**
- `idx_wo_number` on (number)
- `idx_wo_status` on (status)
- `idx_wo_assigned` on (assigned_to)
- `idx_wo_asset` on (asset_id)
- `idx_wo_due_date` on (due_date)

### `work_order_tasks`
Checklist tasks within work orders.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| work_order_id | UUID | FK work_orders(id) | Parent work order |
| title | VARCHAR(200) | NOT NULL | Task title |
| description | TEXT | NULL | Task details |
| sequence | INTEGER | NOT NULL | Display order |
| is_completed | BOOLEAN | DEFAULT false | Completion status |
| completed_by | UUID | FK users(id) NULL | Who completed |
| completed_at | TIMESTAMP | NULL | Completion time |

### `work_order_attachments`
Photos, documents attached to work orders.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| work_order_id | UUID | FK work_orders(id) | Parent work order |
| file_path | TEXT | NOT NULL | Storage path |
| file_name | VARCHAR(255) | NOT NULL | Original filename |
| file_type | VARCHAR(50) | NULL | MIME type |
| uploaded_by | UUID | FK users(id) | Uploader |
| uploaded_at | TIMESTAMP | NOT NULL | Upload time |

### `work_order_time_logs`
Time tracking entries.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| work_order_id | UUID | FK work_orders(id) | Work order reference |
| user_id | UUID | FK users(id) | Technician |
| start_time | TIMESTAMP | NOT NULL | Start time |
| end_time | TIMESTAMP | NULL | End time |
| duration_minutes | INTEGER | NULL | Calculated duration |
| notes | TEXT | NULL | Time log notes |

### `work_order_comments`
Discussion and notes.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| work_order_id | UUID | FK work_orders(id) | Work order reference |
| user_id | UUID | FK users(id) | Commenter |
| comment | TEXT | NOT NULL | Comment text |
| created_at | TIMESTAMP | NOT NULL | Comment time |

---

## 4. Preventive Maintenance

### `maintenance_schedules`
Recurring maintenance schedules.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| name | VARCHAR(200) | NOT NULL | Schedule name |
| asset_id | UUID | FK assets(id) | Target asset |
| frequency_type | VARCHAR(20) | NOT NULL | calendar, meter |
| interval_value | INTEGER | NOT NULL | 30, 100, etc |
| interval_unit | VARCHAR(20) | NOT NULL | days, hours, km |
| next_due_date | TIMESTAMP | NULL | Next execution |
| last_generated_at | TIMESTAMP | NULL | Last WO creation |
| is_active | BOOLEAN | DEFAULT true | Active status |
| created_at | TIMESTAMP | NOT NULL | Creation time |

### `maintenance_templates`
Reusable maintenance procedures.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| name | VARCHAR(200) | NOT NULL | Template name |
| description | TEXT | NULL | Template description |
| category_id | UUID | FK asset_categories(id) | Asset category |
| estimated_hours | DECIMAL(6,2) | NULL | Estimated duration |
| tasks | JSONB | NULL | Task checklist |
| created_at | TIMESTAMP | NOT NULL | Creation time |

### `maintenance_procedures`
Standard operating procedures.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| template_id | UUID | FK maintenance_templates(id) | Template reference |
| step_number | INTEGER | NOT NULL | Step order |
| title | VARCHAR(200) | NOT NULL | Step title |
| instructions | TEXT | NOT NULL | Detailed instructions |
| safety_notes | TEXT | NULL | Safety warnings |

---

## 5. Inventory & Parts

### `inventory_categories`
Parts categorization.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| name | VARCHAR(100) | NOT NULL | Category name |
| parent_id | UUID | FK inventory_categories(id) NULL | Parent category |

### `parts`
Parts and supplies inventory.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| part_number | VARCHAR(100) | UNIQUE, NOT NULL | Part number |
| name | VARCHAR(200) | NOT NULL | Part name |
| description | TEXT | NULL | Part description |
| category_id | UUID | FK inventory_categories(id) | Category reference |
| quantity | INTEGER | NOT NULL, DEFAULT 0 | Current stock |
| unit | VARCHAR(20) | NOT NULL | ea, kg, liter |
| unit_price | DECIMAL(12,2) | NULL | Price per unit |
| min_stock_level | INTEGER | NULL | Reorder point |
| max_stock_level | INTEGER | NULL | Maximum stock |
| location | VARCHAR(100) | NULL | Storage location |
| created_at | TIMESTAMP | NOT NULL | Creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update |

**Indexes:**
- `idx_parts_number` on (part_number)
- `idx_parts_category` on (category_id)
- `idx_parts_low_stock` on (quantity) WHERE quantity <= min_stock_level

### `inventory_transactions`
Stock movements.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| part_id | UUID | FK parts(id) | Part reference |
| transaction_type | VARCHAR(20) | NOT NULL | in, out, adjustment |
| quantity | INTEGER | NOT NULL | Quantity changed |
| unit_price | DECIMAL(12,2) | NULL | Price at transaction |
| reference_type | VARCHAR(50) | NULL | work_order, purchase |
| reference_id | UUID | NULL | Reference ID |
| notes | TEXT | NULL | Transaction notes |
| created_by | UUID | FK users(id) | User who created |
| created_at | TIMESTAMP | NOT NULL | Transaction time |

### `work_order_parts`
Parts consumed in work orders.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| work_order_id | UUID | FK work_orders(id) | Work order reference |
| part_id | UUID | FK parts(id) | Part reference |
| quantity_used | INTEGER | NOT NULL | Quantity consumed |
| unit_price | DECIMAL(12,2) | NULL | Price at time |

### `purchase_requests`
Purchase requisitions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| request_number | VARCHAR(50) | UNIQUE, NOT NULL | PR-2026-001 |
| part_id | UUID | FK parts(id) | Requested part |
| quantity | INTEGER | NOT NULL | Quantity requested |
| urgency | VARCHAR(20) | NULL | normal, urgent |
| status | VARCHAR(50) | NOT NULL | pending, approved, ordered, received |
| requested_by | UUID | FK users(id) | Requester |
| approved_by | UUID | FK users(id) NULL | Approver |
| created_at | TIMESTAMP | NOT NULL | Request time |
| approved_at | TIMESTAMP | NULL | Approval time |

---

## 6. Analytics & Reporting

### `downtime_logs`
Asset downtime tracking.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| asset_id | UUID | FK assets(id) | Asset reference |
| work_order_id | UUID | FK work_orders(id) NULL | Related work order |
| start_time | TIMESTAMP | NOT NULL | Downtime start |
| end_time | TIMESTAMP | NULL | Downtime end |
| duration_minutes | INTEGER | NULL | Calculated duration |
| reason | TEXT | NULL | Downtime reason |
| impact | VARCHAR(50) | NULL | production_loss, safety |

### `metrics_snapshot`
Pre-calculated metrics for performance.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | UUID | PK | Unique identifier |
| metric_type | VARCHAR(50) | NOT NULL | mtbf, mttr, oee |
| entity_type | VARCHAR(50) | NOT NULL | asset, location |
| entity_id | UUID | NOT NULL | Entity reference |
| period_start | DATE | NOT NULL | Period start |
| period_end | DATE | NOT NULL | Period end |
| value | DECIMAL(12,4) | NOT NULL | Metric value |
| calculated_at | TIMESTAMP | NOT NULL | Calculation time |

**Indexes:**
- `idx_metrics_type_entity` on (metric_type, entity_type, entity_id, period_start)

---

## Views

### `v_asset_summary`
Asset overview with statistics.

```sql
CREATE VIEW v_asset_summary AS
SELECT 
  a.id,
  a.code,
  a.name,
  a.status,
  l.name as location_name,
  c.name as category_name,
  COUNT(DISTINCT wo.id) as total_work_orders,
  COUNT(DISTINCT wo.id) FILTER (WHERE wo.status = 'completed') as completed_work_orders,
  SUM(wo.actual_hours) as total_maintenance_hours
FROM assets a
LEFT JOIN locations l ON a.location_id = l.id
LEFT JOIN asset_categories c ON a.category_id = c.id
LEFT JOIN work_orders wo ON wo.asset_id = a.id
GROUP BY a.id, a.code, a.name, a.status, l.name, c.name;
```

---

## Constraints & Business Rules

1. **Asset Code Uniqueness**: Enforced by UNIQUE constraint
2. **Criticality Level**: CHECK (criticality_level BETWEEN 1 AND 5)
3. **Priority Validation**: CHECK (priority BETWEEN 1 AND 5)
4. **Date Logic**: actual_end >= actual_start
5. **Stock Levels**: quantity >= 0

---

## Future Enhancements

- Audit logging with temporal tables
- Soft deletes for critical tables
- Partitioning for work_orders by date
- Full-text search indexes
