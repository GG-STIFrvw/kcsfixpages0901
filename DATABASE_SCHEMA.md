# KCS Auto Repair - Database Schema Documentation

## Database Overview

**Database Name**: `auto_service_db`
**Engine**: InnoDB
**Charset**: utf8mb4_general_ci
**Server**: MariaDB 10.4.32

## Table Relationships

```
users (1) ──────────── (N) vehicles
  │                        │
  │                        │
  └── (1) ──────────── (N) appointments ──── (N) vehicles
              │                    │
              │                    │
              └── (1) ─── (1) job_orders
                          │
                          └── (1) ─── (N) quotations
                                      │
                                      └── (N) ─── (N) quotation_products ──── (N) inventory
```

## Detailed Table Specifications

### 1. users
**Purpose**: Central user management for all system roles

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Unique user identifier |
| username | VARCHAR(100) | UNIQUE | Login username |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| email | VARCHAR(100) | UNIQUE | Email address |
| role | ENUM | 'admin','staff','inventory_manager','mechanic','customer' | User access level |
| full_name | VARCHAR(100) | | Complete name |
| contact_number | VARCHAR(15) | | Phone number |
| home_address | VARCHAR(255) | | Physical address |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Account creation time |
| status | ENUM | 'active','inactive', DEFAULT 'active' | Account status |
| email_verified | TINYINT(1) | DEFAULT 0 | Email verification status |
| verification_token | VARCHAR(255) | | Email verification token |
| profile_picture | VARCHAR(255) | | Profile image filename |

**Indexes**:
- PRIMARY KEY (id)
- UNIQUE KEY (username)
- UNIQUE KEY (email)

### 2. vehicles
**Purpose**: Customer vehicle information

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Vehicle identifier |
| user_id | INT(11) | FOREIGN KEY → users.id | Vehicle owner |
| brand | VARCHAR(50) | | Vehicle manufacturer |
| model | VARCHAR(50) | | Vehicle model |
| plate_number | VARCHAR(20) | | License plate number |

**Relationships**:
- FOREIGN KEY (user_id) REFERENCES users(id)

### 3. appointments
**Purpose**: Service appointment scheduling and management

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Appointment identifier |
| user_id | INT(11) | FOREIGN KEY → users.id | Customer |
| vehicle_id | INT(11) | FOREIGN KEY → vehicles.id | Vehicle to service |
| service_id | INT(11) | FOREIGN KEY → services.id | Requested service |
| scheduled_date | DATE | | Appointment date |
| scheduled_time | TIME | | Appointment time |
| status | ENUM | 'pending','confirmed','in_progress','completed','cancelled','declined' | Current status |
| notes | TEXT | | Additional notes |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Booking time |
| bay_id | INT(11) | FOREIGN KEY → bays.id | Assigned service bay |
| mechanic_id | INT(11) | FOREIGN KEY → users.id | Assigned mechanic |

**Status Flow**:
1. `pending` → Customer books appointment
2. `confirmed` → Staff confirms appointment
3. `in_progress` → Work has started
4. `completed` → Service finished
5. `cancelled` → Customer cancellation
6. `declined` → Staff rejection

### 4. appointment_services
**Purpose**: Many-to-many relationship between appointments and services

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Relationship identifier |
| appointment_id | INT(11) | FOREIGN KEY → appointments.id | Appointment reference |
| service_id | INT(11) | FOREIGN KEY → services.id | Service reference |

### 5. services
**Purpose**: Available repair services catalog

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Service identifier |
| name | VARCHAR(100) | | Service name |
| description | TEXT | | Detailed description |
| cost | DECIMAL(10,2) | | Base service cost |
| estimated_duration | INT | | Duration in minutes |
| is_active | TINYINT(1) | DEFAULT 1 | Service availability |

### 6. bays
**Purpose**: Service bay management and allocation

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Bay identifier |
| bay_name | VARCHAR(50) | | Bay designation |
| is_available | TINYINT(1) | DEFAULT 1 | Availability status |
| equipment_list | TEXT | | Available equipment |
| max_vehicle_size | VARCHAR(50) | | Size limitations |

### 7. job_orders
**Purpose**: Work order management and tracking

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Job order identifier |
| appointment_id | INT(11) | FOREIGN KEY → appointments.id | Related appointment |
| description | TEXT | | Work description |
| status | ENUM | 'pending','in_progress','completed','on_hold' | Current status |
| assigned_mechanic | INT(11) | FOREIGN KEY → users.id | Assigned mechanic |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation time |
| started_at | TIMESTAMP | | Work start time |
| completed_at | TIMESTAMP | | Completion time |
| estimated_hours | DECIMAL(5,2) | | Estimated work time |
| actual_hours | DECIMAL(5,2) | | Actual work time |
| notes | TEXT | | Work notes |

### 8. inventory
**Purpose**: Parts and supplies inventory management

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Inventory item identifier |
| part_name | VARCHAR(100) | | Part name |
| part_number | VARCHAR(50) | UNIQUE | Manufacturer part number |
| description | TEXT | | Detailed description |
| category | VARCHAR(50) | | Part category |
| quantity | INT(11) | DEFAULT 0 | Current stock level |
| unit_price | DECIMAL(10,2) | | Cost per unit |
| reorder_level | INT(11) | DEFAULT 10 | Minimum stock threshold |
| supplier | VARCHAR(100) | | Supplier information |
| location | VARCHAR(50) | | Storage location |
| last_updated | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last modification |

### 9. inventory_log
**Purpose**: Audit trail for inventory changes

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Log entry identifier |
| inventory_id | INT(11) | FOREIGN KEY → inventory.id | Affected item |
| action | ENUM | 'add','remove','update','reorder' | Action type |
| quantity_change | INT(11) | | Quantity delta |
| reason | VARCHAR(255) | | Change reason |
| user_id | INT(11) | FOREIGN KEY → users.id | User who made change |
| timestamp | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Change time |

### 10. quotations
**Purpose**: Service estimates and pricing

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Quotation identifier |
| job_order_id | INT(11) | FOREIGN KEY → job_orders.id | Related job order |
| amount | DECIMAL(10,2) | | Total quoted amount |
| status | ENUM | 'pending','accepted','declined' | Approval status |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation time |
| quote_details | LONGTEXT | | Detailed breakdown |
| decline_note | TEXT | | Reason for decline |
| valid_until | DATE | | Quotation expiry |
| labor_cost | DECIMAL(10,2) | | Labor charges |
| parts_cost | DECIMAL(10,2) | | Parts charges |
| markup_percentage | DECIMAL(5,2) | | Applied markup |

### 11. quotation_products
**Purpose**: Parts included in quotations

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Relationship identifier |
| quotation_id | INT(11) | FOREIGN KEY → quotations.id | Quotation reference |
| product_id | INT(11) | FOREIGN KEY → inventory.id | Inventory item |
| quantity | INT(11) | NOT NULL | Required quantity |
| price_per_unit | DECIMAL(10,2) | NOT NULL | Unit price at time of quote |

### 12. payments
**Purpose**: Payment tracking and billing

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Payment identifier |
| appointment_id | INT(11) | FOREIGN KEY → appointments.id | Related appointment |
| amount | DECIMAL(10,2) | NOT NULL | Payment amount |
| payment_method | ENUM | 'cash','card','bank_transfer','gcash' | Payment type |
| status | ENUM | 'pending','completed','failed','refunded' | Payment status |
| transaction_id | VARCHAR(100) | | External transaction reference |
| payment_date | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Payment time |
| notes | TEXT | | Payment notes |

### 13. notifications
**Purpose**: System notification management

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Notification identifier |
| user_id | INT(11) | FOREIGN KEY → users.id | Recipient |
| title | VARCHAR(255) | | Notification title |
| message | TEXT | | Notification content |
| type | ENUM | 'info','warning','success','error' | Notification type |
| is_read | TINYINT(1) | DEFAULT 0 | Read status |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation time |
| related_entity | VARCHAR(50) | | Related entity type |
| related_id | INT(11) | | Related entity ID |

### 14. logs
**Purpose**: System audit trail and activity logging

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Log entry identifier |
| user_id | INT(11) | FOREIGN KEY → users.id | User who performed action |
| action | VARCHAR(255) | | Description of action |
| entity_type | VARCHAR(50) | | Affected entity type |
| entity_id | INT(11) | | Affected entity ID |
| ip_address | VARCHAR(45) | | Request IP address |
| user_agent | TEXT | | Browser/client information |
| log_time | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Action timestamp |
| additional_data | JSON | | Extra context data |

### 15. password_resets
**Purpose**: Password reset token management

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Reset request identifier |
| email | VARCHAR(100) | | Email address |
| token | VARCHAR(255) | | Reset token |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Request time |
| expires_at | TIMESTAMP | | Token expiry |
| used | TINYINT(1) | DEFAULT 0 | Usage status |

### 16. unavailable_dates
**Purpose**: Calendar blackout dates management

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT(11) | PRIMARY KEY, AUTO_INCREMENT | Blackout identifier |
| date | DATE | NOT NULL | Unavailable date |
| reason | VARCHAR(255) | | Reason for unavailability |
| created_by | INT(11) | FOREIGN KEY → users.id | Admin who set blackout |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation time |

## Data Integrity and Constraints

### Foreign Key Relationships
- All foreign keys use CASCADE updates and RESTRICT deletes
- Orphaned records are prevented through proper constraints
- Referential integrity maintained across all relationships

### Data Validation
- Email addresses validated at application and database level
- Phone numbers formatted consistently
- Enum values strictly enforced
- Decimal precision maintained for financial data

### Indexing Strategy
- Primary keys on all tables
- Unique constraints on usernames and emails
- Foreign key indexes for join performance
- Composite indexes on frequently queried combinations

## Backup and Maintenance

### Recommended Backup Strategy
- Daily full database backups
- Transaction log backups every 15 minutes
- Monthly archive of old appointment data
- Regular integrity checks

### Maintenance Tasks
- Weekly OPTIMIZE TABLE operations
- Monthly cleanup of old logs (>6 months)
- Quarterly review of unused inventory items
- Annual review of inactive user accounts

## Performance Considerations

### Query Optimization
- Use prepared statements for all queries
- Implement proper indexing on join columns
- Consider pagination for large result sets
- Cache frequently accessed data

### Storage Optimization
- Regular cleanup of old notification records
- Archive completed appointments older than 2 years
- Compress large text fields where appropriate
- Monitor table sizes and growth patterns

---

*This schema supports the complete functionality of the KCS Auto Repair Shop Management System while maintaining data integrity and performance.*