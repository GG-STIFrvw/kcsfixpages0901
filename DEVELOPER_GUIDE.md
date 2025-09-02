# KCS Auto Repair - Developer Guide

## Development Environment Setup

### Prerequisites
- **PHP**: 8.2 or higher with extensions:
  - PDO and PDO_MySQL
  - cURL
  - mbstring
  - GD (for image processing)
  - OpenSSL
- **Database**: MySQL 8.0+ or MariaDB 10.4+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Composer**: Latest version
- **Node.js**: 16+ with NPM

### Local Development Setup

#### 1. Environment Preparation
```bash
# Clone the repository
git clone [repository-url] kcs-auto-repair
cd kcs-auto-repair

# Install PHP dependencies
composer install

# Install frontend dependencies
npm install

# Set up file permissions
chmod 755 uploads/
chmod 755 profile_pics/
chmod 644 config.php
```

#### 2. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE auto_service_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

# Import schema and sample data
mysql -u root -p auto_service_db < auto_service_dbgg.sql
```

#### 3. Configuration
```php
// config.php - Update with your settings
$host = 'localhost';
$db = 'auto_service_db';
$user = 'your_db_username';
$pass = 'your_db_password';
```

#### 4. External API Setup
```php
// Email Service (Brevo)
// Add your Brevo API key to relevant files

// SMS Service (Twilio)
// Configure Twilio credentials in SMS-sending files
```

## Code Architecture

### Design Patterns

#### 1. Session-Based Authentication
```php
// Standard authentication check pattern
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$user = $_SESSION['user'];
$role = $user['role'];
```

#### 2. Database Interaction Pattern
```php
// Standard PDO usage pattern
include('config.php');

try {
    $stmt = $pdo->prepare("SELECT * FROM table WHERE column = ?");
    $stmt->execute([$parameter]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    // Handle error appropriately
}
```

#### 3. Role-Based Access Control
```php
// Role checking pattern
function checkRole($required_roles) {
    if (!isset($_SESSION['user'])) {
        return false;
    }
    return in_array($_SESSION['user']['role'], $required_roles);
}

// Usage
if (!checkRole(['admin', 'staff'])) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied");
}
```

#### 4. Logging Pattern
```php
// Standard logging pattern
function logAction($user_id, $action, $entity_type = null, $entity_id = null) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    
    $stmt = $pdo->prepare("
        INSERT INTO logs (user_id, action, entity_type, entity_id, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $action, $entity_type, $entity_id, $ip, $user_agent]);
}
```

### Frontend Architecture

#### CSS Organization
```css
/* Component-specific styling pattern */
.component-container {
    /* Container styles */
}

.component-header {
    /* Header styles */
}

.component-content {
    /* Content styles */
}

.component-actions {
    /* Action button styles */
}

/* Responsive design */
@media (max-width: 768px) {
    .component-container {
        /* Mobile styles */
    }
}
```

#### JavaScript Patterns
```javascript
// AJAX request pattern
function makeAjaxRequest(url, data, callback) {
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            callback(response);
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('An error occurred. Please try again.');
        }
    });
}

// Form validation pattern
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required]');
    
    for (let input of inputs) {
        if (!input.value.trim()) {
            input.classList.add('form-error');
            return false;
        }
        input.classList.remove('form-error');
    }
    return true;
}
```

## Development Guidelines

### Coding Standards

#### PHP Standards
```php
// File structure
<?php
session_start();
include('config.php');

// Class/function definitions
class ClassName {
    private $property;
    
    public function methodName($parameter) {
        // Method implementation
    }
}

// Error handling
try {
    // Database operations
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    // User-friendly error handling
}
?>
```

#### SQL Standards
```sql
-- Use prepared statements
SELECT column1, column2 
FROM table_name 
WHERE condition = ? 
ORDER BY column1 ASC;

-- Proper indexing
CREATE INDEX idx_table_column ON table_name(column_name);

-- Foreign key constraints
ALTER TABLE child_table 
ADD CONSTRAINT fk_parent 
FOREIGN KEY (parent_id) REFERENCES parent_table(id);
```

#### CSS Standards
```css
/* Use consistent naming */
.module-component-element {
    property: value;
}

/* Mobile-first responsive design */
.component {
    /* Base mobile styles */
}

@media (min-width: 768px) {
    .component {
        /* Tablet styles */
    }
}

@media (min-width: 1024px) {
    .component {
        /* Desktop styles */
    }
}
```

### Security Best Practices

#### Input Validation
```php
// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone number
function validatePhone($phone) {
    return preg_match('/^[0-9+\-\s()]+$/', $phone);
}
```

#### SQL Injection Prevention
```php
// Always use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = ?");
$stmt->execute([$email, 'active']);

// Never concatenate user input directly
// BAD: $query = "SELECT * FROM users WHERE id = " . $_POST['id'];
// GOOD: Use prepared statements as shown above
```

#### XSS Prevention
```php
// Escape output
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

// For displaying HTML content
echo htmlspecialchars_decode($safe_html_content);
```

### Database Development

#### Migration Strategy
```sql
-- Version your database changes
-- migration_001_create_users_table.sql
-- migration_002_add_notifications_table.sql
-- migration_003_modify_appointments_status.sql

-- Always include rollback scripts
-- rollback_003_revert_appointments_status.sql
```

#### Query Optimization
```php
// Use appropriate fetch methods
$single_row = $stmt->fetch(PDO::FETCH_ASSOC);
$multiple_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count_only = $stmt->fetchColumn();

// Limit result sets
$stmt = $pdo->prepare("SELECT * FROM table LIMIT ? OFFSET ?");
$stmt->execute([$limit, $offset]);
```

### Testing Guidelines

#### Unit Testing
```php
// Create test files for critical functions
function testUserAuthentication() {
    // Test valid login
    // Test invalid credentials
    // Test rate limiting
    // Test session creation
}

function testInventoryOperations() {
    // Test stock updates
    // Test reorder alerts
    // Test usage tracking
}
```

#### Integration Testing
```php
// Test complete workflows
function testAppointmentBookingFlow() {
    // 1. Customer books appointment
    // 2. Staff confirms appointment
    // 3. Job order created
    // 4. Service completed
    // 5. Invoice generated
}
```

#### Manual Testing Checklist
- [ ] All user roles can access appropriate features
- [ ] Form validation works correctly
- [ ] Email/SMS notifications are sent
- [ ] File uploads work properly
- [ ] Responsive design functions on mobile
- [ ] Cross-browser compatibility verified

## Feature Development

### Adding New Features

#### 1. Database Changes
```sql
-- Create new table if needed
CREATE TABLE new_feature (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    -- other columns
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add foreign keys
ALTER TABLE new_feature 
ADD CONSTRAINT fk_user 
FOREIGN KEY (user_id) REFERENCES users(id);
```

#### 2. Backend Implementation
```php
// Create main feature file
<?php
session_start();
include('config.php');

// Role-based access control
if (!checkRole(['staff', 'admin'])) {
    header("Location: login.php");
    exit();
}

// Feature logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    // Validate input
    // Process data
    // Log action
    // Redirect or show result
}

// Display interface
?>
<!DOCTYPE html>
<html>
<!-- HTML interface -->
</html>
```

#### 3. Frontend Implementation
```css
/* Create feature-specific CSS */
.new-feature-container {
    /* Styling following project conventions */
}
```

```javascript
// Add JavaScript functionality
function handleNewFeature() {
    // Feature-specific logic
}
```

#### 4. Navigation Updates
```php
// Update sidebar.php
if ($role === 'staff' || $role === 'admin') {
    echo '<li><a href="new_feature.php">New Feature</a></li>';
}
```

### API Endpoint Development

#### Standard Endpoint Structure
```php
<?php
session_start();
include('config.php');

// Authentication check
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Input validation
$required_fields = ['field1', 'field2'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit();
    }
}

// Process request
try {
    // Database operations
    $result = performOperation();
    
    // Log action
    logAction($_SESSION['user']['id'], 'Action performed');
    
    // Return success response
    echo json_encode(['success' => true, 'data' => $result]);
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
```

## Debugging and Troubleshooting

### Debug Configuration
```php
// Development environment
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Production environment
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');
```

### Common Issues and Solutions

#### Database Connection Issues
```php
// Check PDO connection
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Database connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
```

#### Session Issues
```php
// Debug session problems
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check session data
var_dump($_SESSION);

// Clear problematic sessions
session_destroy();
```

#### File Upload Issues
```php
// Check upload configuration
echo "Max file size: " . ini_get('upload_max_filesize') . "\n";
echo "Max post size: " . ini_get('post_max_size') . "\n";
echo "Upload directory writable: " . (is_writable('uploads/') ? 'Yes' : 'No');
```

### Logging and Monitoring

#### Application Logging
```php
// Custom logging function
function debugLog($message, $context = []) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
        'file' => debug_backtrace()[0]['file'],
        'line' => debug_backtrace()[0]['line']
    ];
    
    error_log(json_encode($log_entry), 3, 'debug.log');
}

// Usage
debugLog('User login attempt', ['username' => $username, 'ip' => $_SERVER['REMOTE_ADDR']]);
```

#### Performance Monitoring
```php
// Query performance tracking
$start_time = microtime(true);

// Database operation
$stmt = $pdo->prepare($query);
$stmt->execute($params);

$execution_time = microtime(true) - $start_time;
if ($execution_time > 1.0) { // Log slow queries
    error_log("Slow query detected: {$execution_time}s - $query");
}
```

## Deployment Guidelines

### Production Checklist
- [ ] Update database credentials in `config.php`
- [ ] Set proper file permissions (644 for files, 755 for directories)
- [ ] Configure web server virtual host
- [ ] Set up SSL certificate
- [ ] Configure email service credentials
- [ ] Set up automated backups
- [ ] Configure error logging
- [ ] Test all critical functionality
- [ ] Set up monitoring and alerts

### Security Hardening
```php
// Production security settings
ini_set('session.cookie_secure', 1);     // HTTPS only
ini_set('session.cookie_httponly', 1);   // No JavaScript access
ini_set('session.use_strict_mode', 1);   // Strict session handling

// Hide PHP version
header_remove('X-Powered-By');
```

### Performance Optimization
```php
// Enable OPcache
opcache_enable();

// Database connection optimization
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_PERSISTENT => true,  // Connection pooling
];
```

## Code Review Guidelines

### Review Checklist
- [ ] **Security**: No SQL injection vulnerabilities
- [ ] **Authentication**: Proper session checks
- [ ] **Authorization**: Role-based access enforced
- [ ] **Input Validation**: All user input sanitized
- [ ] **Error Handling**: Graceful error management
- [ ] **Logging**: Important actions logged
- [ ] **Performance**: Efficient database queries
- [ ] **Documentation**: Code properly commented

### Code Quality Standards
```php
// Good practices example
class AppointmentManager {
    private $pdo;
    private $logger;
    
    public function __construct(PDO $pdo, Logger $logger) {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }
    
    /**
     * Creates a new appointment with validation
     * @param array $appointmentData Appointment details
     * @return int|false Appointment ID or false on failure
     */
    public function createAppointment(array $appointmentData): int|false {
        // Validate input
        if (!$this->validateAppointmentData($appointmentData)) {
            return false;
        }
        
        try {
            // Database operation
            $stmt = $this->pdo->prepare("
                INSERT INTO appointments (user_id, vehicle_id, service_id, scheduled_date, scheduled_time, notes) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $appointmentData['user_id'],
                $appointmentData['vehicle_id'],
                $appointmentData['service_id'],
                $appointmentData['scheduled_date'],
                $appointmentData['scheduled_time'],
                $appointmentData['notes']
            ]);
            
            if ($result) {
                $appointmentId = $this->pdo->lastInsertId();
                $this->logger->logAction($appointmentData['user_id'], 'Appointment created', 'appointment', $appointmentId);
                return $appointmentId;
            }
            
            return false;
        } catch (PDOException $e) {
            $this->logger->logError('Appointment creation failed', $e->getMessage());
            return false;
        }
    }
    
    private function validateAppointmentData(array $data): bool {
        $required = ['user_id', 'vehicle_id', 'service_id', 'scheduled_date', 'scheduled_time'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        
        // Additional validation logic
        return true;
    }
}
```

## Testing Framework

### Manual Testing Procedures

#### Authentication Testing
```php
// Test cases to verify manually
1. Valid login with correct credentials
2. Invalid login with wrong password
3. Rate limiting after multiple failed attempts
4. Session persistence across pages
5. Proper logout and session cleanup
6. Password reset functionality
7. Email verification process
```

#### Feature Testing Template
```php
// For each new feature, test:
1. Happy path - normal operation
2. Edge cases - boundary conditions
3. Error conditions - invalid input
4. Security - unauthorized access attempts
5. Performance - with realistic data volumes
6. Cross-browser compatibility
7. Mobile responsiveness
```

### Automated Testing Setup
```bash
# Install PHPUnit for PHP testing
composer require --dev phpunit/phpunit

# Create test directory structure
mkdir tests/
mkdir tests/Unit/
mkdir tests/Integration/
```

## Maintenance Procedures

### Regular Maintenance Tasks

#### Daily
- Monitor error logs
- Check system performance
- Verify backup completion
- Review security alerts

#### Weekly
- Update dependencies
- Clean up temporary files
- Review user feedback
- Analyze usage metrics

#### Monthly
- Database optimization
- Security audit
- Performance review
- Documentation updates

### Backup Procedures
```bash
# Database backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p auto_service_db > backups/db_backup_$DATE.sql
gzip backups/db_backup_$DATE.sql

# File backup
tar -czf backups/files_backup_$DATE.tar.gz uploads/ profile_pics/

# Cleanup old backups (keep 30 days)
find backups/ -name "*.sql.gz" -mtime +30 -delete
find backups/ -name "*.tar.gz" -mtime +30 -delete
```

## Contributing Guidelines

### Git Workflow
```bash
# Feature development workflow
git checkout -b feature/new-feature-name
# Make changes
git add .
git commit -m "feat: Add new feature description"
git push origin feature/new-feature-name
# Create pull request
```

### Commit Message Format
```
type(scope): description

feat(auth): add two-factor authentication
fix(billing): resolve payment calculation error
docs(api): update endpoint documentation
style(ui): improve mobile responsiveness
refactor(inventory): optimize stock tracking queries
test(appointments): add booking flow tests
```

### Pull Request Template
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Manual testing completed
- [ ] All existing functionality verified
- [ ] New tests added (if applicable)

## Checklist
- [ ] Code follows project standards
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No sensitive data exposed
```

---

*This developer guide provides comprehensive information for maintaining and extending the KCS Auto Repair Shop Management System. Follow these guidelines to ensure code quality, security, and maintainability.*