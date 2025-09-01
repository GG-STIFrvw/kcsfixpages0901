<?php
// Database connection details
$host = 'localhost'; // Specifies the database server hostname or IP address. 'localhost' is common for a local server.
$db = 'auto_service_db'; // The name of the database to connect to.
$user = 'root'; // The username for the database connection. 'root' is the default for XAMPP.
$pass = ''; // The password for the database user. Default for XAMPP's root user is an empty string.
$charset = 'utf8mb4'; // The character set for the database connection, utf8mb4 is recommended for full Unicode support.

// Data Source Name (DSN) for the PDO connection
// This string combines the database type, host, database name, and character set into a single connection string.
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO connection options
$options = [
    // This option tells PDO to throw exceptions on errors, which makes error handling more predictable.
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];

try {
    // Attempt to create a new PDO (PHP Data Objects) instance to connect to the database.
    // The PDO object is the primary interface for database interaction.
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // If the connection attempt fails, a PDOException is thrown.
    // The script will terminate and display an error message.
    die("DB connection failed: " . $e->getMessage());
}
?>