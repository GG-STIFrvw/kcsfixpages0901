# KCS Auto Repair - File Structure Documentation

## Project Structure Overview

```
kcs-auto-repair/
├── 📁 admin_modules/           # Administrative functionality modules
│   ├── 📁 log_history/         # System logging and audit
│   ├── 📁 maintenance/         # User and system maintenance
│   └── 📁 settings/            # System configuration
├── 📁 css/                     # Stylesheets for all components
├── 📁 modules/                 # Core application modules
│   └── 📁 appointments/        # Appointment-related functionality
├── 📁 node_modules/            # NPM dependencies
├── 📁 profile_pics/            # User profile image storage
├── 📁 resources/               # Additional configuration files
├── 📁 uploads/                 # File upload storage
├── 📁 vendor/                  # Composer dependencies
├── 📁 .git/                    # Git version control
├── 📁 .vscode/                 # VS Code configuration
├── 📄 Core PHP Files           # Main application files
├── 📄 Configuration Files      # Setup and configuration
└── 📄 Documentation Files      # Project documentation
```

## Detailed File Breakdown

### Root Directory Files

#### 🔐 Authentication & Security
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `login.php` | 6.4KB | 151 | User authentication with rate limiting |
| `register.php` | 6.4KB | 128 | User registration with email verification |
| `logout.php` | 511B | 20 | Session termination |
| `verify.php` | 1.4KB | 51 | Email verification processing |
| `verify_otp.php` | 2.5KB | 73 | OTP verification system |
| `forgot_password.php` | 3.1KB | 76 | Password reset request |
| `reset_password.php` | 3.6KB | 88 | Password reset processing |

#### 🏠 Core Application
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `index.php` | 32KB | 590 | Main landing page and service showcase |
| `dashboard.php` | 1.8KB | 63 | Role-based dashboard routing |
| `config.php` | 1.4KB | 28 | Database configuration and PDO setup |
| `header.php` | 1.0KB | 24 | Common HTML header component |
| `navbar.php` | 1.1KB | 27 | Navigation bar with role-based menus |
| `sidebar.php` | 3.7KB | 76 | Dynamic sidebar based on user role |

#### 👥 Customer Interface
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `customer_appointments.php` | 4.3KB | 109 | Customer appointment management |
| `customer_archive.php` | 2.9KB | 77 | Historical appointment data |
| `customer_billing.php` | 5.8KB | 135 | Customer billing and payment interface |
| `customer_booking.php` | 5.5KB | 146 | New appointment booking system |
| `customer_notifications.php` | 6.4KB | 124 | Customer notification center |
| `customer_profile.php` | 12KB | 307 | Customer profile and vehicle management |
| `customer_view_quote.php` | 8.8KB | 202 | Quotation viewing and approval |

#### 👔 Staff Interface
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `staff_appointment_managent.php` | 15KB | 315 | Complete appointment management |
| `staff_all_Job_orders.php` | 6.4KB | 164 | Job order overview and management |
| `staff_bay_manager.php` | 4.1KB | 118 | Service bay allocation |
| `staff_billing.php` | 11KB | 279 | Staff billing interface |
| `staff_calendar_manager.php` | 4.4KB | 144 | Calendar and scheduling |
| `staff_create_JO.php` | 6.0KB | 163 | Job order creation |
| `staff_create_quotation.php` | 14KB | 327 | Quotation creation system |
| `staff_quotation_manager.php` | 1.2KB | 38 | Quotation management overview |
| `staff_sendNotif.php` | 6.1KB | 138 | Notification sending interface |
| `staff_view_quotation.php` | 8.6KB | 205 | Quotation viewing and editing |

#### 📦 Inventory Management
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `inventory_management.php` | 29KB | 604 | Complete inventory management system |
| `inventory_reports.php` | 10KB | 215 | Inventory reporting and analytics |

#### 🛠️ Utility Files
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `fetch_appointments.php` | 2.2KB | 59 | AJAX endpoint for appointment data |
| `update_status.php` | 1.3KB | 36 | Status update handler |
| `send_contact_email.php` | 2.5KB | 67 | Contact form email processing |
| `services_page.php` | 22KB | 534 | Public service information page |
| `terms-and-conditions.php` | 4.0KB | 92 | Legal terms and conditions |
| `quotation_dashboard.php` | 1.7KB | 55 | Quotation overview dashboard |

#### 🧪 Testing & Development
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `test-brevo.php` | 615B | 20 | Brevo email API testing |
| `test_email.php` | 1.1KB | 26 | Email functionality testing |
| `todo_list.php` | 2.9KB | 91 | Development task tracking |
| `job_orders.php` | 499B | 26 | Job order testing/debugging |

#### ⚙️ Configuration Files
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `composer.json` | 178B | 9 | PHP dependency management |
| `composer.lock` | 44KB | 1232 | Locked dependency versions |
| `package.json` | 62B | 6 | NPM dependency configuration |
| `package-lock.json` | 1.2KB | 38 | NPM dependency lock file |
| `.gitignore` | 45B | - | Git ignore rules |

#### 📊 Database
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `auto_service_dbgg.sql` | 28KB | 661 | Complete database schema and sample data |

#### 📄 Documentation
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `README.md` | 557B | 23 | Basic project information |
| `progress` | 659B | 49 | Development progress tracking |

## Module Structure

### `/admin_modules/` - Administrative Functionality

#### `/admin_modules/log_history/`
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `loghistory.php` | 4.4KB | 136 | System log viewing interface |
| `export_logs_excel.php` | 1.8KB | 71 | Excel export functionality |
| `loghistory.css` | 2.7KB | 157 | Log interface styling |
| `composer.json` | 70B | 6 | Module-specific dependencies |
| `composer.lock` | 21KB | 601 | Dependency lock file |

#### `/admin_modules/maintenance/`
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `maintenance.php` | 2.6KB | 77 | User management dashboard |
| `add_user.php` | 3.3KB | 95 | New user creation interface |
| `edit_user.php` | 3.0KB | 101 | User editing interface |
| `toggle_status.php` | 674B | 28 | User status management |
| `add_user.css` | 2.0KB | 115 | Add user form styling |
| `edit_user.css` | 1.8KB | 107 | Edit user form styling |
| `maintenance.css` | 2.0KB | 113 | Maintenance interface styling |

#### `/admin_modules/settings/`
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `admin_settings.php` | 8.7KB | 224 | System settings management |
| `services.php` | 6.6KB | 172 | Service catalog management |
| `services.css` | 2.4KB | 140 | Services interface styling |
| `settings.css` | 3.0KB | 161 | Settings interface styling |

### `/modules/` - Core Application Modules

#### `/modules/appointments/`
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `CDM.php` | 3.0KB | 77 | Customer Data Management |
| `view_customer.php` | 8.9KB | 210 | Detailed customer information view |
| `edit_customer.php` | 2.5KB | 79 | Customer information editing |
| `delete_customer.php` | 749B | 29 | Customer account deletion |
| `update_job_order_status.php` | 1.5KB | 55 | Job order status updates |

### `/css/` - Stylesheet Organization

#### Core Styles
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `common.css` | 1.6KB | 86 | Shared styles across components |
| `dashboard.css` | 12KB | 700 | Main dashboard styling |
| `header.css` | 1.8KB | 111 | Header component styles |
| `dynamic-content.css` | 2.1KB | 145 | Dynamic content loading styles |

#### Authentication Styles
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `login.css` | 1.2KB | 64 | Login form styling |
| `register.css` | 1.5KB | 88 | Registration form styling |
| `verify.css` | 958B | 56 | Email verification styling |

#### Feature-Specific Styles
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `billing.css` | 2.8KB | 164 | Billing interface styling |
| `billing_cust.css` | 2.7KB | 169 | Customer billing view styling |
| `manage_inventory.css` | 3.0KB | 181 | Inventory management styling |
| `service.css` | 10KB | 515 | Service page styling |
| `manage.css` | 4.2KB | 243 | General management interface styling |

#### Component Styles
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `calendar_manager.css` | 2.3KB | 135 | Calendar component styling |
| `bay_manager.css` | 2.8KB | 175 | Bay management styling |
| `qm.css` | 3.8KB | 220 | Quotation management styling |
| `view_customer.css` | 1.7KB | 99 | Customer view styling |
| `my_appointments.css` | 1.6KB | 85 | Customer appointments styling |

### `/resources/` - Additional Resources
| File | Size | Lines | Purpose |
|------|------|-------|---------|
| `config.php` | 367B | 19 | Additional configuration settings |

### `/vendor/` - Composer Dependencies
Contains PHP packages managed by Composer:
- **PHPMailer**: Email sending functionality
- **Sendinblue API**: Email service integration
- **Twilio SDK**: SMS functionality
- **PHPSpreadsheet**: Excel file processing

### `/node_modules/` - NPM Dependencies
Contains frontend JavaScript libraries:
- **jQuery**: DOM manipulation and AJAX
- **DataTables**: Table enhancement and pagination

## File Naming Conventions

### PHP Files
- **Customer files**: `customer_*.php` - Customer-facing functionality
- **Staff files**: `staff_*.php` - Staff-specific interfaces
- **Admin files**: Located in `admin_modules/` directories
- **Utility files**: Single-purpose processing files
- **Module files**: Organized by functionality in subdirectories

### CSS Files
- **Component-specific**: Named after the corresponding PHP file
- **Shared styles**: `common.css`, `header.css`
- **Feature styles**: Named after the feature area

### JavaScript Files
- **Inline scripts**: Embedded in PHP files for component-specific logic
- **External scripts**: `contact_form.js` for contact functionality

## Directory Purposes

### `/uploads/`
**Purpose**: File upload storage
**Contents**: User-uploaded files, documents, images
**Permissions**: Write access for web server
**Security**: File type validation, size limits

### `/profile_pics/`
**Purpose**: User profile image storage
**Contents**: User profile pictures
**Naming**: `{user_id}_{hash}.{extension}`
**Security**: Image validation, size restrictions

### `/vendor/`
**Purpose**: Composer-managed PHP dependencies
**Management**: Managed by Composer
**Version Control**: Excluded from Git (via .gitignore)
**Security**: Regular security updates via Composer

### `/node_modules/`
**Purpose**: NPM-managed JavaScript dependencies
**Management**: Managed by NPM
**Version Control**: Excluded from Git
**Security**: Regular updates via NPM audit

### `/.git/`
**Purpose**: Git version control metadata
**Contents**: Repository history, branches, configuration
**Access**: Developer tools only

### `/.vscode/`
**Purpose**: Visual Studio Code configuration
**Contents**: Editor settings, debugging configuration
**Scope**: Development environment only

## Code Organization Patterns

### MVC-Like Structure
While not strictly MVC, the codebase follows organizational patterns:

#### **Views** (Frontend)
- PHP files with HTML output
- CSS files for styling
- JavaScript for interactivity

#### **Controllers** (Logic)
- PHP files handling form processing
- AJAX endpoints
- Status update handlers

#### **Models** (Data)
- Database interaction through PDO
- SQL queries in PHP files
- Data validation and sanitization

### Module Organization

#### Customer Modules
```
customer_*.php files
├── Authentication flow
├── Profile management
├── Appointment booking
├── Service history
├── Billing interface
└── Notification center
```

#### Staff Modules
```
staff_*.php files
├── Appointment management
├── Job order creation
├── Calendar management
├── Quotation creation
├── Billing operations
└── Customer communication
```

#### Admin Modules
```
admin_modules/
├── log_history/     # Audit and monitoring
├── maintenance/     # User management
└── settings/        # System configuration
```

## File Dependencies

### Core Dependencies
```
config.php
├── Included by: All database-accessing files
├── Provides: PDO connection, database credentials
└── Required for: All CRUD operations
```

### Shared Components
```
header.php
├── Included by: All main pages
├── Provides: HTML head, meta tags, CSS links
└── Ensures: Consistent page structure

navbar.php
├── Included by: All authenticated pages
├── Provides: Navigation menu
└── Adapts to: User role and permissions

sidebar.php
├── Included by: Dashboard and main interfaces
├── Provides: Role-based navigation
└── Loads: Dynamic content based on selections
```

### Authentication Flow
```
login.php
├── Validates: User credentials
├── Creates: User session
├── Redirects: To dashboard.php
└── Logs: Authentication attempts

dashboard.php
├── Checks: Session validity
├── Loads: Role-appropriate interface
├── Includes: navbar.php, sidebar.php
└── Routes: To specific functionality
```

## Security File Considerations

### Sensitive Files
- `config.php` - Contains database credentials
- `composer.json` - May contain API keys
- Files in `/uploads/` and `/profile_pics/` - User data

### Public Files
- `index.php` - Public landing page
- `services_page.php` - Public service information
- `terms-and-conditions.php` - Public legal information
- CSS and JavaScript files - Public assets

### Protected Files
- All `customer_*.php` files - Require customer authentication
- All `staff_*.php` files - Require staff/admin authentication
- All files in `admin_modules/` - Require admin authentication

## Development Workflow

### Adding New Features

#### 1. Customer Feature
```
1. Create customer_feature_name.php
2. Add corresponding CSS file
3. Update sidebar.php navigation
4. Add database tables if needed
5. Update documentation
```

#### 2. Staff Feature
```
1. Create staff_feature_name.php
2. Add corresponding CSS file
3. Update staff navigation
4. Add role-based access checks
5. Update API documentation
```

#### 3. Admin Feature
```
1. Create file in appropriate admin_modules/ subdirectory
2. Add CSS file in same directory
3. Update admin navigation
4. Add proper access controls
5. Update system documentation
```

### File Modification Guidelines

#### PHP Files
- Always include session checks for protected pages
- Use prepared statements for database queries
- Include proper error handling
- Add logging for important actions
- Follow existing code style

#### CSS Files
- Maintain responsive design principles
- Use consistent color scheme (#d63031 primary)
- Follow existing class naming conventions
- Ensure cross-browser compatibility

#### Database Changes
- Update `auto_service_dbgg.sql` schema
- Create migration scripts if needed
- Update database documentation
- Test with existing data

## Maintenance and Cleanup

### Regular Maintenance
- **Log Files**: Clean up old entries (automated)
- **Upload Files**: Remove orphaned uploads
- **Session Files**: Clear expired sessions
- **Cache Files**: Clear temporary cache data

### Development Cleanup
- **Test Files**: Remove debugging files before production
- **Commented Code**: Clean up old commented sections
- **Temporary Files**: Remove development artifacts
- **Debug Output**: Remove debug statements

## Performance Considerations

### File Loading
- **CSS**: Minimize HTTP requests by combining files
- **JavaScript**: Load scripts asynchronously where possible
- **Images**: Optimize profile pictures and uploads
- **PHP**: Use opcode caching (OPcache)

### Database Files
- **Connection Pooling**: Reuse database connections
- **Query Optimization**: Use efficient queries
- **Indexing**: Maintain proper database indexes
- **Caching**: Implement query result caching

---

*This file structure documentation provides a comprehensive overview of the codebase organization, making it easier for developers to navigate and maintain the KCS Auto Repair Shop Management System.*