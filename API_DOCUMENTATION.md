# KCS Auto Repair - API Documentation

## Overview
This document provides detailed information about the API endpoints, request/response formats, and data flow in the KCS Auto Repair Shop Management System.

## Authentication

### Login
**Endpoint**: `POST /login.php`
**Description**: Authenticates user and creates session

**Request Body**:
```php
$_POST['username'] // string: Username or email
$_POST['password'] // string: User password
```

**Response**:
- Success: Redirects to dashboard with session created
- Failure: Error message and rate limiting applied

**Security Features**:
- Rate limiting: 5 attempts per 5-minute window per IP
- IP and user agent logging
- Password verification with PHP's password_verify()

### Registration
**Endpoint**: `POST /register.php`
**Description**: Creates new user account with email verification

**Request Body**:
```php
$_POST['full_name']     // string: User's full name
$_POST['username']      // string: Unique username
$_POST['email']         // string: Email address
$_POST['phone_number']  // string: Phone number
$_POST['password']      // string: Password
$_POST['confirm_password'] // string: Password confirmation
```

**Validation Rules**:
- Username: Minimum 3 characters, unique
- Email: Valid format, unique
- Password: Minimum 8 characters
- Phone: Valid format

### Logout
**Endpoint**: `GET /logout.php`
**Description**: Destroys session and redirects to login

## Appointment Management

### Fetch Appointments
**Endpoint**: `GET /fetch_appointments.php`
**Description**: Retrieves appointment data for calendar display

**Query Parameters**:
```php
$_GET['start'] // string: Start date (YYYY-MM-DD)
$_GET['end']   // string: End date (YYYY-MM-DD)
```

**Response Format**:
```json
[
    {
        "id": "appointment_id",
        "title": "Service Name - Customer Name",
        "start": "2024-01-15T10:00:00",
        "end": "2024-01-15T12:00:00",
        "backgroundColor": "#color_code",
        "borderColor": "#color_code"
    }
]
```

### Create Appointment
**Endpoint**: `POST /customer_booking.php`
**Description**: Creates new service appointment

**Request Body**:
```php
$_POST['vehicle_id']     // int: Selected vehicle ID
$_POST['service_id']     // int: Selected service ID  
$_POST['scheduled_date'] // string: Appointment date
$_POST['scheduled_time'] // string: Appointment time
$_POST['notes']          // string: Additional notes
```

### Update Appointment Status
**Endpoint**: `POST /update_status.php`
**Description**: Updates appointment status

**Request Body**:
```php
$_POST['appointment_id'] // int: Appointment ID
$_POST['status']         // string: New status value
$_POST['notes']          // string: Optional status notes
```

**Valid Status Values**:
- `pending` - Awaiting confirmation
- `confirmed` - Confirmed by staff
- `in_progress` - Work in progress
- `completed` - Service completed
- `cancelled` - Cancelled by customer
- `declined` - Declined by staff

## Customer Management

### View Customer
**Endpoint**: `GET /modules/appointments/view_customer.php`
**Description**: Retrieves detailed customer information

**Query Parameters**:
```php
$_GET['id'] // int: Customer user ID
```

**Response**: HTML page with customer details, vehicles, and service history

### Edit Customer
**Endpoint**: `POST /modules/appointments/edit_customer.php`
**Description**: Updates customer information

**Request Body**:
```php
$_POST['user_id']      // int: Customer ID
$_POST['full_name']    // string: Updated name
$_POST['email']        // string: Updated email
$_POST['phone_number'] // string: Updated phone
```

### Delete Customer
**Endpoint**: `POST /modules/appointments/delete_customer.php`
**Description**: Soft deletes customer account

**Request Body**:
```php
$_POST['user_id'] // int: Customer ID to delete
```

## Inventory Management

### Inventory Operations
**Endpoint**: `POST /inventory_management.php`
**Description**: Handles all inventory CRUD operations

**Add Item**:
```php
$_POST['action'] = 'add'
$_POST['part_name']     // string: Part name
$_POST['part_number']   // string: Part number
$_POST['description']   // string: Part description
$_POST['quantity']      // int: Initial quantity
$_POST['unit_price']    // decimal: Price per unit
$_POST['reorder_level'] // int: Minimum stock level
$_POST['supplier']      // string: Supplier name
```

**Update Item**:
```php
$_POST['action'] = 'update'
$_POST['id']            // int: Inventory item ID
// ... other fields same as add
```

**Delete Item**:
```php
$_POST['action'] = 'delete'
$_POST['id'] // int: Inventory item ID
```

### Inventory Reports
**Endpoint**: `GET /inventory_reports.php`
**Description**: Generates inventory reports and analytics

**Query Parameters**:
```php
$_GET['report_type'] // string: 'low_stock', 'usage', 'valuation'
$_GET['date_from']   // string: Start date for reports
$_GET['date_to']     // string: End date for reports
```

## Job Order Management

### Create Job Order
**Endpoint**: `POST /staff_create_JO.php`
**Description**: Creates new job order from appointment

**Request Body**:
```php
$_POST['appointment_id']    // int: Related appointment
$_POST['description']       // string: Work description
$_POST['estimated_hours']   // decimal: Estimated completion time
$_POST['assigned_mechanic'] // int: Mechanic user ID
$_POST['priority']          // string: 'low', 'medium', 'high'
$_POST['parts_needed'][]    // array: Required inventory items
```

### Update Job Order Status
**Endpoint**: `POST /modules/appointments/update_job_order_status.php`
**Description**: Updates job order progress

**Request Body**:
```php
$_POST['job_order_id'] // int: Job order ID
$_POST['status']       // string: New status
$_POST['notes']        // string: Progress notes
$_POST['hours_worked'] // decimal: Actual hours worked
```

## Quotation Management

### Create Quotation
**Endpoint**: `POST /staff_create_quotation.php`
**Description**: Creates detailed service quotation

**Request Body**:
```php
$_POST['customer_id']      // int: Customer user ID
$_POST['vehicle_id']       // int: Vehicle ID
$_POST['service_type']     // string: Type of service
$_POST['description']      // string: Work description
$_POST['labor_hours']      // decimal: Estimated labor hours
$_POST['labor_rate']       // decimal: Hourly labor rate
$_POST['parts'][]          // array: Required parts with quantities
$_POST['markup_percentage'] // decimal: Parts markup percentage
```

**Parts Array Format**:
```php
$_POST['parts'] = [
    [
        'inventory_id' => 123,
        'quantity' => 2,
        'unit_price' => 25.50
    ]
]
```

### View Quotation
**Endpoints**: 
- `/customer_view_quote.php` (Customer view)
- `/staff_view_quotation.php` (Staff view)

**Query Parameters**:
```php
$_GET['id'] // int: Quotation ID
```

## Notification System

### Send Notification
**Endpoint**: `POST /staff_sendNotif.php`
**Description**: Sends notifications via email/SMS

**Request Body**:
```php
$_POST['user_id']      // int: Recipient user ID
$_POST['message']      // string: Notification message
$_POST['type']         // string: 'email', 'sms', 'both'
$_POST['subject']      // string: Email subject (if email)
$_POST['priority']     // string: 'low', 'medium', 'high'
```

### Contact Form
**Endpoint**: `POST /send_contact_email.php`
**Description**: Processes contact form submissions

**Request Body**:
```php
$_POST['name']    // string: Sender name
$_POST['email']   // string: Sender email
$_POST['phone']   // string: Sender phone
$_POST['message'] // string: Message content
```

## Admin Endpoints

### User Management
**Endpoint**: `POST /admin_modules/maintenance/add_user.php`
**Description**: Creates new system user

**Request Body**:
```php
$_POST['username']     // string: Username
$_POST['email']        // string: Email address
$_POST['full_name']    // string: Full name
$_POST['phone_number'] // string: Phone number
$_POST['role']         // string: 'customer', 'staff', 'admin'
$_POST['password']     // string: Initial password
```

### System Settings
**Endpoint**: `POST /admin_modules/settings/admin_settings.php`
**Description**: Updates system configuration

**Request Body**:
```php
$_POST['setting_name']  // string: Configuration key
$_POST['setting_value'] // string: Configuration value
```

### Service Management
**Endpoint**: `POST /admin_modules/settings/services.php`
**Description**: Manages available services

**Request Body**:
```php
$_POST['action']        // string: 'add', 'update', 'delete'
$_POST['service_name']  // string: Service name
$_POST['description']   // string: Service description
$_POST['base_price']    // decimal: Base service price
$_POST['duration']      // int: Estimated duration in minutes
```

## Data Flow

### Appointment Booking Flow
1. Customer selects vehicle and service
2. System checks bay availability
3. Appointment created with 'pending' status
4. Staff reviews and confirms/declines
5. Customer receives notification
6. Job order created upon confirmation
7. Progress tracking through completion
8. Billing generated upon completion

### Inventory Flow
1. Parts usage recorded in job orders
2. Inventory quantities automatically updated
3. Reorder alerts triggered when below threshold
4. Purchase orders can be generated
5. Stock updates logged for audit trail

### Notification Flow
1. System events trigger notification checks
2. User preferences determine delivery method
3. Templates applied for consistent messaging
4. Delivery attempted via email/SMS
5. Delivery status logged for tracking

## Error Handling

### Standard Error Responses
- **Database Errors**: Logged with full stack trace
- **Validation Errors**: User-friendly messages returned
- **Authentication Errors**: Rate limiting and logging applied
- **Permission Errors**: Access denied with audit logging

### Error Logging
All errors are logged to the `logs` table with:
- User ID (if authenticated)
- Action attempted
- Error message
- IP address
- Timestamp

## Rate Limiting

### Login Protection
- **Window**: 5 minutes
- **Limit**: 5 failed attempts per IP
- **Response**: Account temporarily locked message

### API Protection
- Request logging for monitoring
- Suspicious activity detection
- Automated blocking capabilities

---

*For implementation details and code examples, refer to the specific PHP files mentioned in each endpoint description.*