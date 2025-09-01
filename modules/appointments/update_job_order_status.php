<?php
require '../../config.php';
session_start();

if (!isset($_SESSION['user']['id']) || $_SESSION['user']['role'] !== 'staff') {
    die("Unauthorized access.");
}

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    die("Job Order ID or status not specified.");
}

$job_order_id = $_GET['id'];
$status = $_GET['status'];

// Get customer id for redirection
$stmt = $pdo->prepare("
    SELECT a.user_id
    FROM job_orders jo
    JOIN appointments a ON jo.appointment_id = a.id
    WHERE jo.id = ?
");
$stmt->execute([$job_order_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    die("Job order not found.");
}

$customer_id = $customer['user_id'];

// Get quotation and payment status
$stmt = $pdo->prepare("
    SELECT q.status as quotation_status, p.status as payment_status
    FROM job_orders jo
    LEFT JOIN quotations q ON jo.id = q.job_order_id
    LEFT JOIN payments p ON q.id = p.quotation_id
    WHERE jo.id = ?
");
$stmt->execute([$job_order_id]);
$statuses = $stmt->fetch(PDO::FETCH_ASSOC);

if ($statuses['quotation_status'] !== 'accepted' || $statuses['payment_status'] !== 'paid') {
    $_SESSION['error_message'] = "Cannot mark this job order as completed because the payment for the accepted quotation has not been recorded yet.";
    header("Location: view_customer.php?id=" . $customer_id);
    exit();
}

// Update job order status
$stmt = $pdo->prepare("UPDATE job_orders SET status = ? WHERE id = ?");
$stmt->execute([$status, $job_order_id]);

header("Location: view_customer.php?id=" . $customer_id);
exit();
?>