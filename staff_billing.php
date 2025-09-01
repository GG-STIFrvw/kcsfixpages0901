<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user']['id']) || $_SESSION['user']['role'] !== 'staff') {
    die("Unauthorized access.");
}

$message = '';
$message_type = '';

// Mark GCash payment as paid
if (isset($_GET['mark_paid'])) {
    $payment_id = intval($_GET['mark_paid']);

    $stmt = $pdo->prepare("
        SELECT p.quotation_id, q.job_order_id, jo.appointment_id, a.bay_id
        FROM payments p
        JOIN quotations q ON p.quotation_id = q.id
        JOIN job_orders jo ON q.job_order_id = jo.id
        JOIN appointments a ON jo.appointment_id = a.id
        WHERE p.id = ? AND p.status = 'unverified'
    ");
    $stmt->execute([$payment_id]);
    $data = $stmt->fetch();

    if ($data) {
        $pdo->prepare("UPDATE payments SET status = 'paid' WHERE id = ?")->execute([$payment_id]);
        $pdo->prepare("UPDATE quotations SET status = 'paid' WHERE id = ?")->execute([$data['quotation_id']]);
        $pdo->prepare("UPDATE job_orders SET status = 'completed' WHERE id = ?")->execute([$data['job_order_id']]);
        $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?")->execute([$data['appointment_id']]);
        $pdo->prepare("UPDATE bays SET status = 'Available' WHERE id = ?")->execute([$data['bay_id']]);

        $message = "Payment marked as paid successfully.";
        $message_type = "success";
    } else {
        $message = "Payment not found or already marked as paid.";
        $message_type = "error";
    }
}

// Handle new payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quotation_id = intval($_POST['quotation_id']);
    $payment_method = $_POST['payment_method'];
    $reference = $_POST['reference'] ?? '';
    $upload_valid = true;
    $upload_path = null;

    if ($payment_method === 'gcash') {
        if (isset($_FILES['gcash_receipt']) && $_FILES['gcash_receipt']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = uniqid() . '_' . basename($_FILES['gcash_receipt']['name']);
            $target_file = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['gcash_receipt']['tmp_name'], $target_file)) {
                $upload_path = $target_file;
            } else {
                $upload_valid = false;
                $message = "Failed to upload GCash receipt.";
                $message_type = "error";
            }
        } else {
            $upload_valid = false;
            $message = "GCash receipt image is required.";
            $message_type = "error";
        }

        if (!$upload_valid) {
            // If upload failed, stop processing and display error
            // This will be caught by the HTML part below
        }
    }

    if ($upload_valid) {
        $stmt = $pdo->prepare("
            SELECT q.amount, u.id AS user_id
            FROM quotations q
            JOIN job_orders jo ON q.job_order_id = jo.id
            JOIN appointments a ON jo.appointment_id = a.id
            JOIN users u ON a.user_id = u.id
            WHERE q.id = ? AND q.status = 'accepted'
        ");
        $stmt->execute([$quotation_id]);
        $quotation = $stmt->fetch();

        if (!$quotation) {
            $message = "Quotation not found or not valid.";
            $message_type = "error";
        } else {
            $amount = $quotation['amount'];
            $user_id = $quotation['user_id'];
            $status = ($payment_method === 'cash') ? 'paid' : 'unverified';
            $payment_date = date('Y-m-d');

            $stmt = $pdo->prepare("INSERT INTO payments (user_id, quotation_id, reference_number, amount, status, payment_date, receipt_path)
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $quotation_id, $reference, $amount, $status, $payment_date, $upload_path])) {
                $message = "Payment recorded successfully.";
                $message_type = "success";

                if ($status === 'paid') {
                    $pdo->prepare("UPDATE quotations SET status = 'paid' WHERE id = ?")->execute([$quotation_id]);
                    $pdo->prepare("UPDATE job_orders SET status = 'completed' WHERE id = (SELECT job_order_id FROM quotations WHERE id = ?)")->execute([$quotation_id]);

                    $stmt = $pdo->prepare("
                        SELECT jo.appointment_id, a.bay_id
                        FROM quotations q
                        JOIN job_orders jo ON q.job_order_id = jo.id
                        JOIN appointments a ON jo.appointment_id = a.id
                        WHERE q.id = ?
                    ");
                    $stmt->execute([$quotation_id]);
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($data) {
                        $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?")->execute([$data['appointment_id']]);
                        $pdo->prepare("UPDATE bays SET status = 'available' WHERE id = ?")->execute([$data['bay_id']]);
                    }
                }
            } else {
                $message = "Failed to record payment.";
                $message_type = "error";
            }
        }
    }
}

// Load quotations
$stmt = $pdo->query("
    SELECT q.id, q.amount, u.full_name
    FROM quotations q
    JOIN job_orders jo ON q.job_order_id = jo.id
    JOIN appointments a ON jo.appointment_id = a.id
    JOIN users u ON a.user_id = u.id
    WHERE q.status = 'accepted'
");
$quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load all payments
$payments_stmt = $pdo->query("
    SELECT p.*, u.full_name
    FROM payments p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.payment_date DESC
");
$payments = $payments_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Management</title>
    <link rel="stylesheet" href="css/billing.css">
</head>
<body>

    <div class="header">
        <h1>Billing Management</h1>
        <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h2>Record New Payment</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="quotation_id">Select Quotation:</label>
                    <select name="quotation_id" id="quotation_id" required>
                        <option value="">-- Select Accepted Quotation --</option>
                        <?php foreach ($quotations as $q): ?>
                            <option value="<?= $q['id'] ?>">
                                #<?= $q['id'] ?> - <?= htmlspecialchars($q['full_name']) ?> (₱<?= number_format($q['amount'], 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="payment_method">Payment Method:</label>
                    <select name="payment_method" id="payment_method" onchange="toggleGCash(this.value === 'gcash')" required>
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                    </select>
                </div>

                <div id="gcash_fields" style="display:none;">
                    <div class="form-group">
                        <label for="reference">GCash Reference Number:</label>
                        <input type="text" name="reference" id="reference" placeholder="Enter GCash reference number">
                    </div>
                    <div class="form-group">
                        <label for="gcash_receipt">Upload GCash Receipt:</label>
                        <input type="file" name="gcash_receipt" id="gcash_receipt" accept="image/*">
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit">Record Payment</button>
                </div>
            </form>
        </div>

        <div class="form-section">
            <h2>All Payments</h2>
            <?php if (!empty($payments)): ?>
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>Quotation ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Receipt</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?= $p['quotation_id'] ?></td>
                            <td><?= htmlspecialchars($p['full_name']) ?></td>
                            <td>₱<?= number_format($p['amount'], 2) ?></td>
                            <td><?= empty($p['reference_number']) ? 'Cash' : 'GCash' ?></td>
                            <td><?= htmlspecialchars($p['reference_number'] ?? '-') ?></td>
                            <td><span class="status-<?= $p['status'] ?>"><?= htmlspecialchars(ucfirst($p['status'])) ?></span></td>
                            <td><?= $p['payment_date'] ?></td>
                            <td>
                                <?php if ($p['receipt_path']): ?>
                                    <a href="<?= htmlspecialchars($p['receipt_path']) ?>" target="_blank">
                                        <img src="<?= htmlspecialchars($p['receipt_path']) ?>" alt="Receipt">
                                    </a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['status'] === 'unverified'): ?>
                                    <a href="?mark_paid=<?= $p['id'] ?>" class="action-button" onclick="return confirm('Mark this GCash payment as paid?');">Mark as Paid</a>
                                <?php elseif ($p['status'] === 'paid'): ?>
                                    <a href="staff_sendNotif.php?quotation_id=<?= $p['quotation_id'] ?>" class="action-button">Send Notification</a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No payments recorded yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function toggleGCash(show) {
        document.getElementById('gcash_fields').style.display = show ? 'block' : 'none';
        document.getElementById('reference').required = show;
        document.getElementById('gcash_receipt').required = show;
    }
    </script>

</body>
</html>