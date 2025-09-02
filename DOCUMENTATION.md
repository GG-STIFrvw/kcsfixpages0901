# KCS Auto Repair Shop - Complete System Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [File Structure](#file-structure)
5. [Features](#features)
6. [User Roles](#user-roles)
7. [Installation Guide](#installation-guide)
8. [API Endpoints](#api-endpoints)
9. [Security Features](#security-features)
10. [Dependencies](#dependencies)

## System Overview

KCS Auto Repair Shop Management System is a comprehensive web-based application designed to manage auto repair shop operations. The system handles appointment scheduling, inventory management, customer management, billing, quotations, and staff workflow management.

### Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript, TailwindCSS
- **Backend**: PHP 8.2+
- **Database**: MySQL/MariaDB
- **Email Service**: Brevo API, PHPMailer
- **SMS Service**: Twilio SDK
- **File Processing**: PHPSpreadsheet
- **Version Control**: Git

### Key Features
- 🗓️ **Appointment Management**: Online booking, scheduling, and tracking
- 📦 **Inventory Management**: Parts tracking, stock management, automated alerts
- 👥 **Customer Management**: Profile management, service history, billing
- 💰 **Billing & Quotations**: Invoice generation, payment tracking, estimates
- 🔧 **Job Order Management**: Work order creation, progress tracking
- 📊 **Reporting**: Service reports, inventory reports, analytics
- 🔐 **Multi-role Authentication**: Customer, Staff, Admin access levels
- 📱 **Notifications**: Email and SMS notifications
- 🏗️ **Bay Management**: Service bay allocation and scheduling

## Architecture

### System Architecture
The application follows a traditional MVC-like pattern with PHP handling both frontend rendering and backend logic:

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   Database      │
│   (HTML/CSS/JS) │◄──►│   (PHP)         │◄──►│   (MySQL)       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │              ┌─────────────────┐              │
         └─────────────►│  External APIs  │◄─────────────┘
                        │  (Brevo, Twilio)│
                        └─────────────────┘
```

### Application Flow
1. **Authentication**: Users log in through `login.php` with role-based access
2. **Dashboard**: Role-specific dashboard (`dashboard.php`) loads appropriate interface
3. **Modules**: Functionality organized into modules for different features
4. **Database**: All data operations handled through PDO with prepared statements
5. **Notifications**: Automated email/SMS through external APIs

## Database Schema

### Core Tables

#### Users Table
- **Purpose**: Stores user accounts for customers, staff, and administrators
- **Key Fields**: `id`, `username`, `email`, `role`, `full_name`, `phone_number`
- **Roles**: customer, staff, admin

#### Appointments Table
- **Purpose**: Manages service appointments and scheduling
- **Key Fields**: `id`, `user_id`, `vehicle_id`, `service_id`, `scheduled_date`, `status`
- **Status Values**: pending, confirmed, in_progress, completed, cancelled, declined

#### Vehicles Table
- **Purpose**: Stores customer vehicle information
- **Key Fields**: `id`, `user_id`, `make`, `model`, `year`, `license_plate`

#### Services Table
- **Purpose**: Defines available repair services
- **Key Fields**: `id`, `service_name`, `description`, `base_price`

#### Inventory Table
- **Purpose**: Manages parts and supplies inventory
- **Key Fields**: `id`, `part_name`, `part_number`, `quantity`, `unit_price`, `reorder_level`

#### Job Orders Table
- **Purpose**: Tracks work orders and repair progress
- **Key Fields**: `id`, `appointment_id`, `description`, `status`, `assigned_mechanic`

#### Quotations Table
- **Purpose**: Manages repair estimates and quotes
- **Key Fields**: `id`, `customer_id`, `vehicle_id`, `total_amount`, `status`

#### Payments Table
- **Purpose**: Tracks billing and payment information
- **Key Fields**: `id`, `appointment_id`, `amount`, `payment_method`, `status`

#### Notifications Table
- **Purpose**: Manages system notifications and alerts
- **Key Fields**: `id`, `user_id`, `message`, `type`, `is_read`

#### Logs Table
- **Purpose**: System audit trail and activity logging
- **Key Fields**: `id`, `user_id`, `action`, `ip_address`, `log_time`

### Database Relationships
- Users → Vehicles (1:N)
- Users → Appointments (1:N)
- Vehicles → Appointments (1:N)
- Services → Appointments (1:N)
- Appointments → Job Orders (1:1)
- Appointments → Payments (1:N)
- Users → Notifications (1:N)

## File Structure

### Root Directory Files

#### Core Application Files
- **`index.php`** (590 lines): Main landing page with service information and booking interface
- **`login.php`** (151 lines): User authentication with rate limiting and security features
- **`register.php`** (128 lines): User registration with email verification
- **`dashboard.php`** (63 lines): Role-based dashboard routing
- **`config.php`** (28 lines): Database configuration and PDO setup
- **`logout.php`** (20 lines): Session termination

#### Shared Components
- **`header.php`** (24 lines): Common HTML header
- **`navbar.php`** (27 lines): Navigation bar component
- **`sidebar.php`** (76 lines): Role-based sidebar navigation

#### Customer Interface Files
- **`customer_appointments.php`** (109 lines): Customer appointment management
- **`customer_archive.php`** (77 lines): Historical appointment data
- **`customer_billing.php`** (135 lines): Customer billing and payment interface
- **`customer_booking.php`** (146 lines): New appointment booking system
- **`customer_notifications.php`** (124 lines): Customer notification center
- **`customer_profile.php`** (307 lines): Customer profile management
- **`customer_view_quote.php`** (202 lines): Quotation viewing interface

#### Staff Interface Files
- **`staff_appointment_managent.php`** (315 lines): Staff appointment management
- **`staff_all_Job_orders.php`** (164 lines): Job order overview and management
- **`staff_bay_manager.php`** (118 lines): Service bay allocation management
- **`staff_billing.php`** (279 lines): Staff billing interface
- **`staff_calendar_manager.php`** (144 lines): Calendar and scheduling management
- **`staff_create_JO.php`** (163 lines): Job order creation interface
- **`staff_create_quotation.php`** (327 lines): Quotation creation system
- **`staff_quotation_manager.php`** (38 lines): Quotation management overview
- **`staff_sendNotif.php`** (138 lines): Staff notification sending interface
- **`staff_view_quotation.php`** (205 lines): Quotation viewing and editing

#### Inventory Management
- **`inventory_management.php`** (604 lines): Complete inventory management system
- **`inventory_reports.php`** (215 lines): Inventory reporting and analytics

#### Utility Files
- **`fetch_appointments.php`** (59 lines): AJAX endpoint for appointment data
- **`update_status.php`** (36 lines): Status update handler
- **`send_contact_email.php`** (67 lines): Contact form email handler
- **`forgot_password.php`** (76 lines): Password reset functionality
- **`reset_password.php`** (88 lines): Password reset processing
- **`verify.php`** (51 lines): Email verification handler
- **`verify_otp.php`** (73 lines): OTP verification system

#### Service Pages
- **`services_page.php`** (534 lines): Public service information page
- **`terms-and-conditions.php`** (92 lines): Legal terms page
- **`quotation_dashboard.php`** (55 lines): Quotation dashboard overview

#### Testing Files
- **`test-brevo.php`** (20 lines): Brevo email API testing
- **`test_email.php`** (26 lines): Email functionality testing
- **`todo_list.php`** (91 lines): Development task tracking

### Directory Structure

#### `/modules/`
- **`appointments/`**: Appointment-related modules
  - `CDM.php` (77 lines): Customer Data Management
  - `delete_customer.php` (29 lines): Customer deletion handler
  - `edit_customer.php` (79 lines): Customer editing interface
  - `update_job_order_status.php` (55 lines): Job order status updates
  - `view_customer.php` (210 lines): Customer detail view

#### `/admin_modules/`
- **`log_history/`**: System logging and audit
  - `loghistory.php` (136 lines): Log viewing interface
  - `export_logs_excel.php` (71 lines): Log export functionality
  - `loghistory.css` (157 lines): Log interface styling
  
- **`maintenance/`**: User and system maintenance
  - `maintenance.php` (77 lines): User management interface
  - `add_user.php` (95 lines): User creation interface
  - `edit_user.php` (101 lines): User editing interface
  - `toggle_status.php` (28 lines): User status management
  
- **`settings/`**: System configuration
  - `admin_settings.php` (224 lines): Administrative settings interface
  - `services.php` (172 lines): Service management
  - Associated CSS files for styling

#### `/css/`
Contains 25+ CSS files for styling different components:
- **Core Styles**: `common.css`, `dashboard.css`, `header.css`
- **Feature Styles**: `billing.css`, `manage_inventory.css`, `service.css`
- **Form Styles**: `login.css`, `register.css`, `verify.css`
- **Module Styles**: Component-specific styling files

#### `/resources/`
- **`config.php`** (19 lines): Additional configuration settings

#### `/uploads/`, `/profile_pics/`
- File storage directories for user uploads and profile images

#### `/vendor/`
- Composer dependencies and autoloader

#### `/node_modules/`
- NPM dependencies (jQuery, DataTables)

## Features

### 1. User Management
- **Registration**: Email verification, role assignment
- **Authentication**: Secure login with rate limiting
- **Profile Management**: Personal information, vehicle details
- **Password Recovery**: Email-based password reset with OTP

### 2. Appointment System
- **Online Booking**: Customer self-service appointment scheduling
- **Calendar Management**: Staff calendar interface with bay allocation
- **Status Tracking**: Real-time appointment status updates
- **Notifications**: Automated email/SMS reminders and updates

### 3. Inventory Management
- **Stock Tracking**: Real-time inventory levels
- **Reorder Alerts**: Automated low-stock notifications
- **Usage Logging**: Track parts usage in job orders
- **Reporting**: Inventory reports and analytics

### 4. Job Order Management
- **Work Order Creation**: Detailed job descriptions and requirements
- **Progress Tracking**: Real-time status updates
- **Mechanic Assignment**: Task allocation to specific technicians
- **Parts Integration**: Link inventory usage to specific jobs

### 5. Quotation System
- **Estimate Creation**: Detailed quotations with parts and labor
- **Customer Approval**: Quote approval workflow
- **Quote Management**: Staff interface for quote handling
- **Integration**: Convert approved quotes to job orders

### 6. Billing & Payments
- **Invoice Generation**: Automated billing based on completed work
- **Payment Tracking**: Record and track customer payments
- **Payment Methods**: Support for multiple payment types
- **Customer Billing History**: Complete payment records

### 7. Notification System
- **Email Notifications**: Appointment reminders, status updates
- **SMS Alerts**: Critical notifications via Twilio
- **In-App Notifications**: Dashboard notification center
- **Automated Triggers**: Event-based notification sending

### 8. Reporting & Analytics
- **Appointment Reports**: Service statistics and trends
- **Inventory Reports**: Stock levels and usage patterns
- **Financial Reports**: Revenue and payment tracking
- **System Logs**: Complete audit trail

## User Roles

### Customer Role
**Access Level**: Limited to personal data and services

**Available Features**:
- View and book appointments
- Manage personal profile and vehicles
- View service history and billing
- Receive notifications
- View and approve quotations

**Key Files**:
- `customer_appointments.php`
- `customer_booking.php`
- `customer_profile.php`
- `customer_billing.php`
- `customer_notifications.php`
- `customer_view_quote.php`

### Staff Role
**Access Level**: Operational management and customer service

**Available Features**:
- Manage all appointments and scheduling
- Create and manage job orders
- Handle inventory operations
- Generate quotations and invoices
- Send notifications to customers
- View customer information
- Manage service bays and calendar

**Key Files**:
- `staff_appointment_managent.php`
- `staff_all_Job_orders.php`
- `staff_bay_manager.php`
- `staff_billing.php`
- `staff_calendar_manager.php`
- `staff_create_JO.php`
- `staff_create_quotation.php`
- `staff_sendNotif.php`

### Admin Role
**Access Level**: Full system administration

**Available Features**:
- All staff features
- User management (create, edit, deactivate users)
- System settings configuration
- Service management
- Complete system logs and audit trail
- System maintenance operations

**Key Files**:
- `admin_modules/maintenance/maintenance.php`
- `admin_modules/settings/admin_settings.php`
- `admin_modules/log_history/loghistory.php`

## Installation Guide

### Prerequisites
- PHP 8.2 or higher
- MySQL/MariaDB 10.4+
- Apache/Nginx web server
- Composer (for PHP dependencies)
- NPM (for frontend dependencies)

### Setup Steps

1. **Clone Repository**
   ```bash
   git clone [repository-url]
   cd kcs-auto-repair
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install Frontend Dependencies**
   ```bash
   npm install
   ```

4. **Database Setup**
   ```bash
   # Import the database schema
   mysql -u root -p < auto_service_dbgg.sql
   ```

5. **Configuration**
   - Update `config.php` with your database credentials
   - Configure email settings in relevant files
   - Set up Twilio credentials for SMS functionality

6. **Web Server Configuration**
   - Point document root to the project directory
   - Ensure PHP extensions are enabled: PDO, mysqli, curl, mbstring

7. **File Permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 profile_pics/
   ```

### Environment Variables
Configure the following in your environment or directly in config files:
- Database credentials
- Brevo API key
- Twilio API credentials
- SMTP settings

## API Endpoints

### Authentication Endpoints
- **POST** `/login.php` - User authentication
- **POST** `/register.php` - User registration
- **POST** `/forgot_password.php` - Password reset request
- **POST** `/reset_password.php` - Password reset processing
- **POST** `/verify.php` - Email verification
- **POST** `/verify_otp.php` - OTP verification

### Appointment Management
- **GET** `/fetch_appointments.php` - Retrieve appointment data
- **POST** `/customer_booking.php` - Create new appointment
- **POST** `/update_status.php` - Update appointment status

### Customer Management
- **GET** `/modules/appointments/view_customer.php` - View customer details
- **POST** `/modules/appointments/edit_customer.php` - Update customer information
- **POST** `/modules/appointments/delete_customer.php` - Remove customer

### Inventory Operations
- **GET/POST** `/inventory_management.php` - Inventory CRUD operations
- **GET** `/inventory_reports.php` - Inventory reporting

### Notification System
- **POST** `/staff_sendNotif.php` - Send notifications
- **POST** `/send_contact_email.php` - Contact form processing

## Security Features

### Authentication Security
- **Rate Limiting**: 5 failed attempts per 5-minute window
- **Password Hashing**: Secure password storage
- **Session Management**: Secure session handling
- **IP Tracking**: Login attempt monitoring

### Data Protection
- **SQL Injection Prevention**: Prepared statements throughout
- **Input Validation**: Server-side validation on all inputs
- **XSS Protection**: Output escaping and sanitization
- **CSRF Protection**: Form token validation

### Access Control
- **Role-Based Access**: Strict role enforcement
- **Session Validation**: Authentication checks on protected pages
- **Admin Controls**: Administrative override capabilities

### Audit Trail
- **System Logs**: Complete activity logging in `logs` table
- **User Actions**: Detailed action tracking
- **IP Logging**: Request source tracking
- **Export Capabilities**: Log export for compliance

## Dependencies

### PHP Dependencies (Composer)
```json
{
    "phpmailer/phpmailer": "^6.10",
    "sendinblue/api-v3-sdk": "^8.4", 
    "twilio/sdk": "^8.6",
    "phpoffice/phpspreadsheet": "*"
}
```

### Frontend Dependencies (NPM)
```json
{
    "jquery": "latest",
    "datatables.net": "latest"
}
```

### External CDN Resources
- **TailwindCSS**: Modern utility-first CSS framework
- **Font Awesome**: Icon library
- **Google Fonts**: Poppins font family

## File Organization

### Frontend Assets
- **CSS**: `/css/` - Component-specific stylesheets
- **JavaScript**: Inline and external scripts for interactivity
- **Images**: Various directories for uploads and assets

### Backend Logic
- **Core Files**: Root directory PHP files
- **Modules**: `/modules/` and `/admin_modules/` for organized functionality
- **Configuration**: `config.php` and related configuration files

### Data Storage
- **Database**: MySQL schema in `auto_service_dbgg.sql`
- **File Uploads**: `/uploads/` and `/profile_pics/`
- **Logs**: Database-stored with export capabilities

## Development Notes

### Code Quality
- Consistent PHP coding standards
- Comprehensive error handling
- Extensive commenting throughout codebase
- Modular architecture for maintainability

### Testing
- Test files included for email functionality
- Brevo API testing capabilities
- Development progress tracking

### Scalability Considerations
- Modular design allows for easy feature addition
- Database design supports growth
- Role-based architecture enables team expansion
- API-ready structure for future integrations

---

*This documentation provides a comprehensive overview of the KCS Auto Repair Shop Management System. For specific implementation details, refer to the individual file comments and code documentation.*