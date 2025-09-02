# KCS Auto Repair - Complete Setup Guide

## Prerequisites

### System Requirements
- **Operating System**: Linux, Windows, or macOS
- **PHP**: Version 8.2 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.4+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: Minimum 512MB RAM
- **Storage**: At least 2GB free space

### Required PHP Extensions
```bash
# Check if extensions are installed
php -m | grep -E "(pdo|pdo_mysql|curl|mbstring|gd|openssl|json|fileinfo)"
```

Required extensions:
- `pdo` - Database abstraction layer
- `pdo_mysql` - MySQL database driver
- `curl` - HTTP client for API calls
- `mbstring` - Multibyte string handling
- `gd` - Image processing
- `openssl` - Encryption and security
- `json` - JSON data handling
- `fileinfo` - File type detection

### Development Tools
- **Composer**: PHP dependency manager
- **Node.js**: JavaScript runtime (16+)
- **NPM**: Node package manager
- **Git**: Version control system

## Installation Methods

### Method 1: XAMPP (Recommended for Development)

#### 1. Install XAMPP
```bash
# Download from https://www.apachefriends.org/
# Install XAMPP with Apache, MySQL, and PHP
```

#### 2. Setup Project
```bash
# Navigate to XAMPP htdocs
cd /opt/lampp/htdocs  # Linux
cd C:\xampp\htdocs    # Windows

# Clone repository
git clone [repository-url] kcs-auto-repair
cd kcs-auto-repair
```

#### 3. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install frontend dependencies
npm install
```

#### 4. Database Setup
```bash
# Start XAMPP services
sudo /opt/lampp/lampp start  # Linux
# Or use XAMPP Control Panel on Windows

# Access phpMyAdmin: http://localhost/phpmyadmin
# Create database: auto_service_db
# Import: auto_service_dbgg.sql
```

#### 5. Configuration
```php
// config.php - Default XAMPP settings
$host = 'localhost';
$db = 'auto_service_db';
$user = 'root';
$pass = '';  // Empty for XAMPP default
```

#### 6. File Permissions
```bash
# Set proper permissions
chmod 755 uploads/
chmod 755 profile_pics/
chmod 644 *.php
```

### Method 2: Docker Setup

#### 1. Create Docker Environment
```dockerfile
# Dockerfile
FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli curl mbstring gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/
RUN chmod 755 /var/www/html/uploads/
RUN chmod 755 /var/www/html/profile_pics/

EXPOSE 80
```

```yaml
# docker-compose.yml
version: '3.8'
services:
  web:
    build: .
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=auto_service_db
      - DB_USER=root
      - DB_PASSWORD=rootpassword

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: auto_service_db
    volumes:
      - db_data:/var/lib/mysql
      - ./auto_service_dbgg.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "3306:3306"

volumes:
  db_data:
```

#### 2. Start Docker Environment
```bash
# Build and start containers
docker-compose up -d

# Access application: http://localhost:8080
```

### Method 3: Production Server Setup

#### 1. Server Preparation (Ubuntu/Debian)
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install LAMP stack
sudo apt install apache2 mysql-server php8.2 php8.2-mysql php8.2-curl php8.2-mbstring php8.2-gd php8.2-xml php8.2-zip -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

#### 2. Apache Configuration
```apache
# /etc/apache2/sites-available/kcs-auto-repair.conf
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/kcs-auto-repair
    
    <Directory /var/www/kcs-auto-repair>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/kcs_error.log
    CustomLog ${APACHE_LOG_DIR}/kcs_access.log combined
</VirtualHost>
```

```bash
# Enable site and modules
sudo a2ensite kcs-auto-repair.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### 3. Database Setup
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
mysql -u root -p
```

```sql
CREATE DATABASE auto_service_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'kcs_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON auto_service_db.* TO 'kcs_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Import schema
mysql -u kcs_user -p auto_service_db < auto_service_dbgg.sql
```

#### 4. Application Deployment
```bash
# Clone to web directory
cd /var/www/
sudo git clone [repository-url] kcs-auto-repair
cd kcs-auto-repair

# Install dependencies
sudo composer install --no-dev --optimize-autoloader
sudo npm install --production

# Set permissions
sudo chown -R www-data:www-data /var/www/kcs-auto-repair/
sudo chmod 755 uploads/ profile_pics/
sudo chmod 644 config.php
```

#### 5. SSL Setup (Let's Encrypt)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Get SSL certificate
sudo certbot --apache -d your-domain.com

# Auto-renewal setup
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

## Configuration

### Database Configuration
```php
// config.php - Production settings
$host = 'localhost';
$db = 'auto_service_db';
$user = 'kcs_user';
$pass = 'your_secure_password';
$charset = 'utf8mb4';

// Production PDO options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_EMULATE_PREPARES => false,
];
```

### Email Configuration

#### Brevo API Setup
```php
// In files using Brevo API
$config = \SendinBlue\Client\Configuration::getDefaultConfiguration()
    ->setApiKey('api-key', 'your-brevo-api-key');
```

#### PHPMailer Configuration
```php
// Email settings
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';  // Or your SMTP server
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

### SMS Configuration (Twilio)
```php
// Twilio setup
$account_sid = 'your-account-sid';
$auth_token = 'your-auth-token';
$twilio_number = '+1234567890';

$twilio = new \Twilio\Rest\Client($account_sid, $auth_token);
```

## Environment-Specific Setup

### Development Environment
```php
// Development settings
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug mode
define('DEBUG_MODE', true);
```

### Staging Environment
```php
// Staging settings
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php/error.log');

// Limited debug
define('DEBUG_MODE', false);
```

### Production Environment
```php
// Production security settings
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php/kcs_errors.log');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Disable debug mode
define('DEBUG_MODE', false);
```

## Initial Data Setup

### Default Admin Account
```sql
-- Create default admin user
INSERT INTO users (username, password, email, role, full_name, status, email_verified) 
VALUES (
    'admin', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 'password'
    'admin@kcsautorepair.com',
    'admin',
    'System Administrator',
    'active',
    1
);
```

### Sample Services
```sql
-- Add basic services
INSERT INTO services (name, description, cost, estimated_duration) VALUES
('Oil Change', 'Complete oil and filter change', 1500.00, 60),
('Brake Service', 'Brake inspection and pad replacement', 3500.00, 120),
('Engine Diagnostic', 'Computer diagnostic scan', 2000.00, 45),
('Tire Rotation', 'Tire rotation and pressure check', 800.00, 30),
('Battery Test', 'Battery testing and replacement', 2500.00, 30);
```

### Service Bays
```sql
-- Add service bays
INSERT INTO bays (bay_name, equipment_list, max_vehicle_size) VALUES
('Bay 1', 'Hydraulic lift, air compressor, diagnostic equipment', 'Full-size truck'),
('Bay 2', 'Quick-lube equipment, vacuum', 'Standard car'),
('Bay 3', 'Alignment equipment, tire changer', 'SUV'),
('Bay 4', 'Engine hoist, welding equipment', 'Full-size truck');
```

## Testing Your Installation

### Basic Functionality Test
1. **Access Homepage**: Navigate to your domain/localhost
2. **User Registration**: Create a test customer account
3. **Email Verification**: Check email verification process
4. **Login**: Test authentication system
5. **Appointment Booking**: Create a test appointment
6. **Staff Functions**: Test with staff account
7. **Admin Access**: Verify admin functionality

### Database Connection Test
```php
// test_db.php
<?php
include('config.php');

try {
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch();
    echo "Database connected successfully. User count: " . $result['user_count'];
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>
```

### Email Test
```php
// Run test_email.php
// Should send test email to verify email functionality
```

### SMS Test
```php
// Run test with Twilio credentials
// Verify SMS sending capability
```

## Troubleshooting

### Common Installation Issues

#### Database Connection Errors
```bash
# Check MySQL service
sudo systemctl status mysql

# Check MySQL logs
sudo tail -f /var/log/mysql/error.log

# Test connection
mysql -u username -p -h localhost
```

#### Permission Issues
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/kcs-auto-repair/
sudo chmod -R 755 /var/www/kcs-auto-repair/
sudo chmod 755 uploads/ profile_pics/
```

#### PHP Extension Issues
```bash
# Install missing extensions (Ubuntu)
sudo apt install php8.2-pdo php8.2-mysql php8.2-curl php8.2-mbstring php8.2-gd

# Restart web server
sudo systemctl restart apache2
```

#### Composer Issues
```bash
# Clear Composer cache
composer clear-cache

# Reinstall dependencies
rm -rf vendor/
composer install
```

#### Email/SMS Issues
- Verify API credentials
- Check firewall settings
- Test with external email service
- Validate phone number formats

### Performance Optimization

#### PHP Configuration
```ini
; php.ini optimizations
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 10M
post_max_size = 10M
max_input_vars = 3000

; OPcache settings
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
```

#### MySQL Configuration
```ini
# my.cnf optimizations
[mysqld]
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
query_cache_size = 32M
query_cache_type = 1
max_connections = 100
```

#### Apache Configuration
```apache
# Enable compression
LoadModule deflate_module modules/mod_deflate.so

<Location />
    SetOutputFilter DEFLATE
    SetEnvIfNoCase Request_URI \
        \.(?:gif|jpe?g|png)$ no-gzip dont-vary
</Location>

# Enable caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
</IfModule>
```

## Security Setup

### File Permissions
```bash
# Secure file permissions
find /var/www/kcs-auto-repair -type f -exec chmod 644 {} \;
find /var/www/kcs-auto-repair -type d -exec chmod 755 {} \;

# Writable directories
chmod 755 uploads/ profile_pics/

# Protect sensitive files
chmod 600 config.php
```

### Apache Security
```apache
# .htaccess for additional security
# Disable directory browsing
Options -Indexes

# Protect sensitive files
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

<Files "*.sql">
    Order allow,deny
    Deny from all
</Files>

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

### Database Security
```sql
-- Remove default accounts
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');

-- Create application-specific user
CREATE USER 'kcs_app'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON auto_service_db.* TO 'kcs_app'@'localhost';
FLUSH PRIVILEGES;
```

## Backup Configuration

### Automated Database Backup
```bash
#!/bin/bash
# backup_db.sh

# Configuration
DB_NAME="auto_service_db"
DB_USER="kcs_user"
DB_PASS="your_password"
BACKUP_DIR="/var/backups/kcs"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Create backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/db_backup_$DATE.sql

# Remove backups older than 30 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete

echo "Backup completed: db_backup_$DATE.sql.gz"
```

### File Backup Script
```bash
#!/bin/bash
# backup_files.sh

BACKUP_DIR="/var/backups/kcs"
DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/kcs-auto-repair"

# Backup uploads and profile pictures
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz -C $APP_DIR uploads/ profile_pics/

# Remove old file backups
find $BACKUP_DIR -name "files_backup_*.tar.gz" -mtime +30 -delete

echo "File backup completed: files_backup_$DATE.tar.gz"
```

### Cron Job Setup
```bash
# Edit crontab
sudo crontab -e

# Add backup jobs
# Daily database backup at 2 AM
0 2 * * * /path/to/backup_db.sh

# Weekly file backup on Sundays at 3 AM
0 3 * * 0 /path/to/backup_files.sh

# Monthly log cleanup
0 4 1 * * find /var/log/php/ -name "*.log" -mtime +30 -delete
```

## Monitoring Setup

### Log Monitoring
```bash
# Create log monitoring script
#!/bin/bash
# monitor_logs.sh

LOG_FILE="/var/log/php/kcs_errors.log"
ERROR_COUNT=$(grep -c "ERROR" $LOG_FILE)

if [ $ERROR_COUNT -gt 10 ]; then
    echo "High error count detected: $ERROR_COUNT errors"
    # Send alert email
    mail -s "KCS Auto Repair - High Error Count" admin@kcsautorepair.com < $LOG_FILE
fi
```

### System Health Check
```php
// health_check.php
<?php
$health_status = [
    'database' => false,
    'uploads' => false,
    'email' => false,
    'disk_space' => false
];

// Check database
try {
    include('config.php');
    $stmt = $pdo->query("SELECT 1");
    $health_status['database'] = true;
} catch (Exception $e) {
    error_log("Health check - Database failed: " . $e->getMessage());
}

// Check upload directories
$health_status['uploads'] = is_writable('uploads/') && is_writable('profile_pics/');

// Check disk space (90% threshold)
$disk_free = disk_free_space('.');
$disk_total = disk_total_space('.');
$disk_used_percent = (($disk_total - $disk_free) / $disk_total) * 100;
$health_status['disk_space'] = $disk_used_percent < 90;

// Return status
header('Content-Type: application/json');
echo json_encode([
    'status' => array_sum($health_status) === count($health_status) ? 'healthy' : 'unhealthy',
    'checks' => $health_status,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
```

## Maintenance Procedures

### Regular Maintenance Tasks

#### Daily
```bash
# Check error logs
tail -f /var/log/php/kcs_errors.log

# Monitor disk space
df -h

# Check service status
systemctl status apache2 mysql
```

#### Weekly
```bash
# Update system packages
sudo apt update && sudo apt upgrade

# Check for PHP/Composer updates
composer outdated

# Review security logs
grep -i "failed\|error\|attack" /var/log/apache2/kcs_access.log
```

#### Monthly
```bash
# Database optimization
mysql -u root -p -e "OPTIMIZE TABLE auto_service_db.appointments, auto_service_db.logs, auto_service_db.notifications;"

# Clean up old logs
find /var/log/ -name "*.log" -mtime +30 -delete

# Update dependencies
composer update
npm update
```

## Scaling Considerations

### Load Balancing Setup
```apache
# Apache load balancer configuration
<Proxy balancer://kcs-cluster>
    BalancerMember http://server1:80
    BalancerMember http://server2:80
    ProxySet lbmethod=byrequests
</Proxy>

ProxyPass / balancer://kcs-cluster/
ProxyPassReverse / balancer://kcs-cluster/
```

### Database Scaling
```sql
-- Read replica setup
-- Configure MySQL master-slave replication
-- Update application to use read replicas for SELECT queries
```

### Caching Implementation
```php
// Redis caching example
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

// Cache frequently accessed data
$cache_key = "appointments_" . date('Y-m-d');
$appointments = $redis->get($cache_key);

if (!$appointments) {
    // Fetch from database
    $appointments = fetchAppointmentsFromDB();
    $redis->setex($cache_key, 3600, serialize($appointments)); // Cache for 1 hour
}
```

## Support and Maintenance

### Getting Help
- **Documentation**: Refer to provided documentation files
- **Issue Tracking**: Use Git issues for bug reports
- **Community**: Check existing issues and discussions

### Updating the System
```bash
# Update application
git pull origin main
composer install --no-dev
npm install --production

# Update database if needed
mysql -u username -p auto_service_db < migrations/new_migration.sql

# Clear caches
rm -rf cache/*
```

### Rollback Procedures
```bash
# Database rollback
mysql -u username -p auto_service_db < backups/db_backup_YYYYMMDD.sql

# Application rollback
git checkout previous-stable-commit
composer install --no-dev
```

---

*This setup guide provides comprehensive instructions for installing, configuring, and maintaining the KCS Auto Repair Shop Management System across different environments.*