<?php
require '../../config.php';
session_start();

if (!isset($_SESSION['user']['id']) || $_SESSION['user']['role'] !== 'staff') {
    die("Unauthorized access.");
}

if (!isset($_GET['id'])) {
    die("Customer ID not specified.");
}

$customer_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
    $stmt->execute([$customer_id]);
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        $_SESSION['error_message'] = "Cannot delete this customer because they have associated records (e.g., vehicles, appointments).";
    } else {
        $_SESSION['error_message'] = "An error occurred while trying to delete the customer.";
    }
}

header("Location: CDM.php");
exit();
?>
