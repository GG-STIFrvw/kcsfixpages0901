<?php
require 'config.php';
include 'header.php';
session_start();

if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quotation_id = intval($_POST['quotation_id']);
    $payment_method = $_POST['payment_method'];
    $reference = $_POST['reference'] ?? '';
    $upload_path = null;

    if ($payment_method === 'gcash') {
        if (isset($_FILES['gcash_receipt']) && $_FILES['gcash_receipt']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            $filename = time() . '_' . uniqid() . '_' . basename($_FILES['gcash_receipt']['name']);
            $target_file = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['gcash_receipt']['tmp_name'], $target_file)) {
                $upload_path = $target_file;
            } else {
                $message = "<div class='message error'>Sorry, there was an error uploading your file.</div>";
            }
        } else {
            $message = "<div class='message error'>A GCash receipt image is required for this payment method.</div>";
        }
    }

    if (empty($message)) {
        $stmt = $pdo->prepare("SELECT amount FROM quotations WHERE id = ? AND status = 'accepted' AND id IN (SELECT q.id FROM quotations q JOIN job_orders jo ON q.job_order_id = jo.id JOIN appointments a ON jo.appointment_id = a.id WHERE a.user_id = ?)");
        $stmt->execute([$quotation_id, $user_id]);
        $quotation = $stmt->fetch();

        if (!$quotation) {
            $message = "<div class='message error'>Quotation not found or you are not authorized to pay for it.</div>";
        } else {
            $amount = $quotation['amount'];
            $status = ($payment_method === 'cash') ? 'paid' : 'unverified';
            $payment_date = date('Y-m-d');

            $stmt = $pdo->prepare("INSERT INTO payments (user_id, quotation_id, reference_number, amount, status, payment_date, receipt_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $quotation_id, $reference, $amount, $status, $payment_date, $upload_path])) {
                $message = "<div class='message success'>Payment recorded successfully.</div>";

                $update_status = ($status === 'paid') ? 'paid' : 'pending_payment_verification';
                $pdo->prepare("UPDATE quotations SET status = ? WHERE id = ?")->execute([$update_status, $quotation_id]);

                if ($status === 'paid') {
                    $pdo->prepare("UPDATE job_orders SET status = 'completed' WHERE id = (SELECT job_order_id FROM quotations WHERE id = ?)")->execute([$quotation_id]);
                    $stmt = $pdo->prepare("SELECT jo.appointment_id, a.bay_id FROM quotations q JOIN job_orders jo ON q.job_order_id = jo.id JOIN appointments a ON jo.appointment_id = a.id WHERE q.id = ?");
                    $stmt->execute([$quotation_id]);
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($data) {
                        $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?")->execute([$data['appointment_id']]);
                        $pdo->prepare("UPDATE bays SET status = 'Available' WHERE id = ?")->execute([$data['bay_id']]);
                    }
                }
            } else {
                $message = "<div class='message error'>Failed to record payment. Please try again.</div>";
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT q.id, q.amount, jo.id as job_order_id FROM quotations q JOIN job_orders jo ON q.job_order_id = jo.id JOIN appointments a ON jo.appointment_id = a.id WHERE q.status = 'accepted' AND a.user_id = ?");
$stmt->execute([$user_id]);
$quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing | KCS Auto Service</title>
    <link rel="stylesheet" href="css/billing_cust.css">
</head>
<body>
    <div class="pt-24"></div>


  <div class="billing-container">
    <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Submit Your GCash Payment</h1>
    
    <?php echo $message; ?>

    <div class="payment-layout">
        <!-- GCash QR section -->
        <div class="gcash-info">
            <img src="https://i.ibb.co/NdTVcMD9/gcash-qr.png" alt="GCash QR Code" class="qr-image">
            <p class="gcash-number">GCash Number: </p>
            <p class="gcash"><strong>0917-123-4567</strong></p>
        </div>

        <!-- Payment form -->
        <form class="billing-form" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="quotation_id">Select Quotation to Pay:</label>
                <select name="quotation_id" id="quotation_id" required>
                    <option value="" disabled selected>-- Choose an Unpaid Quotation --</option>
                    <?php foreach ($quotations as $q): ?>
                        <option value="<?= $q['id'] ?>">
                            Job Order #<?= $q['job_order_id'] ?> - ₱<?= number_format($q['amount'], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" name="payment_method" value="gcash">

            <div class="form-group">
                <label for="reference">GCash Reference No:</label>
                <input type="text" name="reference" id="reference" required>
            </div>

            <div class="form-group">
                <label for="gcash_receipt">Upload GCash Receipt:</label>
                <input type="file" name="gcash_receipt" id="gcash_receipt" accept="image/*" required>
            </div>

            <button type="submit">Submit Payment</button>
        </form>
    </div>
</div>


</body>
</html>
