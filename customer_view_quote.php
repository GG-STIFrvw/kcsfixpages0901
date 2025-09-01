<?php
session_start();
require_once 'config.php';
include 'header.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quote_id = intval($_POST['quote_id']);
    $status = $_POST['status'];  
    $note = ($status === 'declined') ? ($_POST['note'] ?? '') : null;

    $stmt = $pdo->prepare("UPDATE quotations SET status = ?, decline_note = ? WHERE id = ?");
    if ($stmt->execute([$status, $note, $quote_id])) {
        $message = "<div class='message'>Quotation status updated successfully.</div>";
    }

    if ($status === 'accepted') {
        $stmt = $pdo->prepare("SELECT qp.product_id, qp.quantity FROM quotation_products qp WHERE qp.quotation_id = ?");
        $stmt->execute([$quote_id]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $product) {
            $stmt = $pdo->prepare("UPDATE inventory SET quantity = GREATEST(quantity - ?, 0) WHERE id = ?");
            $stmt->execute([$product['quantity'], $product['product_id']]);
        }
    }

    if ($status === 'declined') {
        $stmt = $pdo->prepare("SELECT a.id AS appointment_id, a.bay_id FROM quotations q JOIN job_orders jo ON q.job_order_id = jo.id JOIN appointments a ON jo.appointment_id = a.id WHERE q.id = ?");
        $stmt->execute([$quote_id]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($appointment) {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'canceled' WHERE id = ?");
            $stmt->execute([$appointment['appointment_id']]);

            $stmt = $pdo->prepare("UPDATE bays SET status = 'Available' WHERE id = ?");
            $stmt->execute([$appointment['bay_id']]);
        }
    }
}

$customerId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT q.id, q.job_order_id, q.quote_details, q.amount, q.status, q.decline_note, jo.diagnosis, s.name AS service_name
    FROM quotations q
    JOIN job_orders jo ON q.job_order_id = jo.id
    JOIN appointments a ON jo.appointment_id = a.id
    JOIN services s ON a.service_id = s.id
    WHERE a.user_id = ? ORDER BY q.id DESC
");
$stmt->execute([$customerId]);
$allQuotations = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getQuotationProducts($pdo, $quotation_id) {
    $stmt = $pdo->prepare("SELECT qp.quantity, qp.price_per_unit, i.item_name FROM quotation_products qp JOIN inventory i ON qp.product_id = i.id WHERE qp.quotation_id = ?");
    $stmt->execute([$quotation_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Quotations | KCS Auto Service</title>
    <link rel="stylesheet" href="css/view.css">
</head>
<body>

    <div class="header">
        <h1>Your Quotations</h1>
        <a href="dashboard_customer.php" class="back-link">Back to Dashboard</a>
    </div>

    <div class="container">
        <?php echo $message; ?>

        <h2>Pending Quotations</h2>
        <?php 
        $pendingQuotations = array_filter($allQuotations, fn($q) => $q['status'] === 'pending');
        if (!empty($pendingQuotations)): 
            foreach ($pendingQuotations as $quote):
        ?>
            <div class="quote-card status-pending">
                <div class="quote-content">
                    <div class="quote-header">
                        <h3>Quotation for Job Order #<?= $quote['job_order_id'] ?></h3>
                        <span class="quote-status status-pending">Pending</span>
                    </div>
                    <div class="quote-body">
                        <p><strong>Service:</strong> <?= htmlspecialchars($quote['service_name']) ?></p>
                        <p><strong>Diagnosis:</strong> <?= htmlspecialchars($quote['diagnosis']) ?></p>
                        <p><strong>Details:</strong> <?= nl2br(htmlspecialchars($quote['quote_details'])) ?></p>
                        <p><strong>Total Amount:</strong> ₱<?= number_format($quote['amount'], 2) ?></p>
                        
                        <?php $products = getQuotationProducts($pdo, $quote['id']); ?>
                        <?php if (!empty($products)): ?>
                            <div class="product-list">
                                <h4>Included Products:</h4>
                                <ul>
                                    <?php foreach ($products as $prod): ?>
                                        <li>
                                            <span><?= htmlspecialchars($prod['item_name']) ?> (<?= $prod['quantity'] ?> x ₱<?= number_format($prod['price_per_unit'], 2) ?>)</span>
                                            <strong>₱<?= number_format($prod['quantity'] * $prod['price_per_unit'], 2) ?></strong>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="quote-actions">
                        <form method="POST" onsubmit="return confirmAction(this, 'accept');" style="display: inline;">
                            <input type="hidden" name="quote_id" value="<?= $quote['id'] ?>">
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit" class="accept-btn">Accept</button>
                        </form>
                        <button class="decline-btn" onclick="showDecline(this)">Decline</button>
                        <div class="decline-note">
                            <form method="POST" onsubmit="return confirmAction(this, 'decline');">
                                <input type="hidden" name="quote_id" value="<?= $quote['id'] ?>">
                                <input type="hidden" name="status" value="declined">
                                <textarea name="note" placeholder="Please provide a reason for declining..." rows="3" required style="height: 84px;width: 100%;"> </textarea>
                                <button type="submit" class="confirm-decline-btn">Confirm Decline</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php 
            endforeach; 
        else: 
        ?>
            <p>No pending quotations available.</p>
        <?php endif; ?>

        <h2>Archived Quotations</h2>
        <?php 
        $archivedQuotations = array_filter($allQuotations, fn($q) => $q['status'] !== 'pending');
        if (!empty($archivedQuotations)): 
            foreach ($archivedQuotations as $quote):
                $statusClass = 'status-' . htmlspecialchars($quote['status']);
        ?>
            <div class="quote-card <?= $statusClass ?>">
                <div class="quote-content">
                    <div class="quote-header">
                        <h3>Quotation for Job Order #<?= $quote['job_order_id'] ?></h3>
                        <span class="quote-status <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($quote['status'])) ?></span>
                    </div>
                    <div class="quote-body">
                        <p><strong>Service:</strong> <?= htmlspecialchars($quote['service_name']) ?></p>
                        <p><strong>Total Amount:</strong> ₱<?= number_format($quote['amount'], 2) ?></p>
                        <?php if ($quote['status'] === 'declined' && !empty($quote['decline_note'])): ?>
                            <div class="decline-reason">
                                <strong>Reason for Decline:</strong> <?= htmlspecialchars($quote['decline_note']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php 
            endforeach; 
        else: 
        ?>
            <p>No archived quotations available.</p>
        <?php endif; ?>
    </div>

    <script>
    function showDecline(button) {
        var actionsDiv = button.parentElement;
        actionsDiv.querySelector('.decline-note').style.display = 'block';
        button.style.display = 'none';
        actionsDiv.querySelector('.accept-btn').style.display = 'none';
    }

    function confirmAction(form, action) {
        if (action === 'accept') {
            return confirm('Are you sure you want to accept this quotation?');
        }
        if (action === 'decline') {
            const note = form.querySelector('textarea[name="note"]');
            if (!note.value.trim()) {
                alert('Please provide a reason for declining.');
                return false;
            }
            return confirm('Are you sure you want to decline this quotation?');
        }
        return false;
    }
    </script>

</body>
</html>