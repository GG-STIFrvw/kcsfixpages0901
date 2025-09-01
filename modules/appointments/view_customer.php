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

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    die("Customer not found.");
}

// Fetch job orders for the customer
$job_orders_stmt = $pdo->prepare("
    SELECT jo.*, a.scheduled_date, s.name as service_name, v.brand, v.model, v.plate_number, q.id as quotation_id, q.status as quotation_status, p.status as payment_status
    FROM job_orders jo
    JOIN appointments a ON jo.appointment_id = a.id
    LEFT JOIN services s ON a.service_id = s.id
    JOIN vehicles v ON a.vehicle_id = v.id
    LEFT JOIN quotations q ON jo.id = q.job_order_id
    LEFT JOIN payments p ON q.id = p.quotation_id
    WHERE a.user_id = ?
    ORDER BY a.scheduled_date DESC
");
$job_orders_stmt->execute([$customer_id]);
$job_orders = $job_orders_stmt->fetchAll(PDO::FETCH_ASSOC);

$active_job_orders = array_filter($job_orders, function($jo) {
    return $jo['status'] !== 'completed';
});

$completed_job_orders = array_filter($job_orders, function($jo) {
    return $jo['status'] === 'completed';
});

// Fetch payments for the customer
$payments_stmt = $pdo->prepare("
    SELECT p.*, q.amount
    FROM payments p
    JOIN quotations q ON p.quotation_id = q.id
    WHERE p.user_id = ?
    ORDER BY p.payment_date DESC
");
$payments_stmt->execute([$customer_id]);
$payments = $payments_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer Information</title>
    <link rel="stylesheet" href="../../css/view_customer.css">
</head>
<body>

    <div class="header">
        <h1>View Customer Information</h1>
        <a href="CDM.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Customer List</a>
    </div>

    <div class="container">
        <div class="customer-details">
            <h2><?= htmlspecialchars($customer['full_name']) ?></h2>
            <p><strong>Email:</strong> <?= htmlspecialchars($customer['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($customer['contact_number']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($customer['home_address']) ?></p>
        </div>

        <div class="job-orders-section">
            <h3>Active Job Orders</h3>
            <?php if (!empty($active_job_orders)) : ?>
                <table class="job-orders-table">
                    <thead>
                        <tr>
                            <th>Job Order ID</th>
                            <th>Appointment Date</th>
                            <th>Vehicle</th>
                            <th>Service Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($active_job_orders as $jo) : ?>
                            <tr>
                                <td><?= htmlspecialchars($jo['id']) ?></td>
                                <td><?= htmlspecialchars($jo['scheduled_date']) ?></td>
                                <td><?= htmlspecialchars($jo['brand'] . ' ' . $jo['model'] . ' (' . $jo['plate_number'] . ')') ?></td>
                                <td><?= htmlspecialchars($jo['service_name']) ?></td>
                                <td><?= htmlspecialchars($jo['status']) ?></td>
                                <td class="actions">
                                    <?php 
                                        $is_disabled = !($jo['quotation_status'] === 'accepted' && $jo['payment_status'] === 'paid');
                                        $href = $is_disabled ? 'javascript:void(0)' : 'update_job_order_status.php?id=' . $jo['id'] . '&status=completed';
                                        $class = 'action-btn complete-btn' . ($is_disabled ? ' disabled-btn' : '');
                                        $title = $is_disabled ? 'Payment has not been recorded for the accepted quotation.' : '';
                                    ?>
                                    <a href="<?= $href ?>" class="<?= $class ?>" title="<?= $title ?>">Mark as Completed</a>
                                    <a href="../../staff_billing.php?job_order_id=<?= $jo['id'] ?>" class="action-btn view-jo-btn">View Billing</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No active job orders found.</p>
            <?php endif; ?>
        </div>

        <div class="job-orders-section">
            <h3>Completed Job Orders</h3>
            <?php if (!empty($completed_job_orders)) : ?>
                <table class="job-orders-table">
                    <thead>
                        <tr>
                            <th>Job Order ID</th>
                            <th>Appointment Date</th>
                            <th>Vehicle</th>
                            <th>Service Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completed_job_orders as $jo) : ?>
                            <tr>
                                <td><?= htmlspecialchars($jo['id']) ?></td>
                                <td><?= htmlspecialchars($jo['scheduled_date']) ?></td>
                                <td><?= htmlspecialchars($jo['brand'] . ' ' . $jo['model'] . ' (' . $jo['plate_number'] . ')') ?></td>
                                <td><?= htmlspecialchars($jo['service_name']) ?></td>
                                <td><?= htmlspecialchars($jo['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No completed job orders found.</p>
            <?php endif; ?>
        </div>

        <div class="payments-section">
            <h3>Payment History</h3>
            <?php if (!empty($payments)) : ?>
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>Quotation ID</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p) : ?>
                            <tr>
                                <td><?= htmlspecialchars($p['quotation_id']) ?></td>
                                <td>₱<?= number_format($p['amount'], 2) ?></td>
                                <td><?= empty($p['reference_number']) ? 'Cash' : 'GCash' ?></td>
                                <td><?= htmlspecialchars($p['reference_number'] ?? '-') ?></td>
                                <td><span class="status-<?= htmlspecialchars($p['status']) ?>"><?= htmlspecialchars(ucfirst($p['status'])) ?></span></td>
                                <td><?= htmlspecialchars($p['payment_date']) ?></td>
                                <td>
                                    <?php if ($p['receipt_path']) : ?>
                                        <a href="../../<?= htmlspecialchars($p['receipt_path']) ?>" target="_blank">
                                            <img src="../../<?= htmlspecialchars($p['receipt_path']) ?>" alt="Receipt" width="100">
                                        </a>
                                    <?php else : ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No payment history found.</p>
            <?php endif; ?>
        </div>
    </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const disabledLinks = document.querySelectorAll('.disabled-btn');
        disabledLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                alert('This action is locked. The payment for the accepted quotation has not been recorded yet.');
            });
        });
    });
</script>
</body>
</html>
