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
    <title>Your Quotations | KCS Auto Repair Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/cust_view_quote.css">
</head>

<div class="header-space"></div> <!-- Space for fixed header -->
<br><br><br><br><br>

<body class="bg-gray-100 text-gray-800">

    <main class="px-8 max-w-5xl mx-auto bg-white shadow rounded-lg">
        <!-- Page Title -->
        <!--<h2 class="text-2xl font-semibold mb-6 text-gray-800">Your Quotations</h2> COMMENTeD OUT FOR CONSISTENCY-->

        <!-- Pending Quotations -->
        <h1 class="text-2xl font-semibold mb-6 px-4 py-3 bg-white shadow rounded-lg">
            Pending Quotations
        </h1>

        <?php 
        $pendingQuotations = array_filter($allQuotations, fn($q) => $q['status'] === 'pending');
        if (!empty($pendingQuotations)): 
            foreach ($pendingQuotations as $quote):
        ?>
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Quotation for Job Order #<?= $quote['job_order_id'] ?></h3>
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-700">
                        Pending
                    </span>
                </div>

                <div class="space-y-2 text-gray-700">
                    <p><strong>Service:</strong> <?= htmlspecialchars($quote['service_name']) ?></p>
                    <p><strong>Diagnosis:</strong> <?= htmlspecialchars($quote['diagnosis']) ?></p>
                    <p><strong>Details:</strong> <?= nl2br(htmlspecialchars($quote['quote_details'])) ?></p>
                    <p><strong>Total Amount:</strong> ₱<?= number_format($quote['amount'], 2) ?></p>
                </div>

                <?php $products = getQuotationProducts($pdo, $quote['id']); ?>
                <?php if (!empty($products)): ?>
                    <div class="mt-4">
                        <h4 class="font-medium mb-2">Included Products:</h4>
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($products as $prod): ?>
                                <li class="flex justify-between py-2">
                                    <span><?= htmlspecialchars($prod['item_name']) ?> (<?= $prod['quantity'] ?> × ₱<?= number_format($prod['price_per_unit'], 2) ?>)</span>
                                    <strong>₱<?= number_format($prod['quantity'] * $prod['price_per_unit'], 2) ?></strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="mt-6 flex flex-wrap gap-2">
                    <form method="POST" onsubmit="return confirmAction(this, 'accept');">
                        <input type="hidden" name="quote_id" value="<?= $quote['id'] ?>">
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit" class="accept-btn">Accept</button>
                    </form>

                    <button onclick="showDecline(this)" class="decline-btn">
                        Decline
                    </button>

                    <div class="decline-note w-full hidden">
                        <form method="POST" onsubmit="return confirmAction(this, 'decline');" class="mt-3">
                            <input type="hidden" name="quote_id" value="<?= $quote['id'] ?>">
                            <input type="hidden" name="status" value="declined">
                            <textarea name="note" placeholder="Please provide a reason for declining..." rows="3" required></textarea>
                            <button type="submit" class="confirm-decline-btn">Confirm Decline</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php 
            endforeach; 
        else: 
        ?>
            <p class="text-gray-500 mb-6">No pending quotations available.</p>
        <?php endif; ?>


        <!-- Archived Quotations -->
        <h2 class="text-2xl font-semibold mb-6 px-4 py-3 bg-white shadow rounded-lg">
            Archived Quotations
        </h2>

        <?php 
        $archivedQuotations = array_filter($allQuotations, fn($q) => $q['status'] !== 'pending');
        if (!empty($archivedQuotations)): 
            foreach ($archivedQuotations as $quote):
                $statusClass = $quote['status'] === 'accepted' 
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700';
        ?>
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Quotation for Job Order #<?= $quote['job_order_id'] ?></h3>
                    <span class="px-3 py-1 rounded-full text-sm font-medium <?= $statusClass ?>">
                        <?= ucfirst(htmlspecialchars($quote['status'])) ?>
                    </span>
                </div>

                <div class="text-gray-700">
                    <p><strong>Service:</strong> <?= htmlspecialchars($quote['service_name']) ?></p>
                    <p><strong>Total Amount:</strong> ₱<?= number_format($quote['amount'], 2) ?></p>
                    
                    <?php if ($quote['status'] === 'declined' && !empty($quote['decline_note'])): ?>
                        <div class="mt-2 bg-red-50 border-l-4 border-red-400 text-red-700 p-3 rounded">
                            <strong>Reason for Decline:</strong> <?= htmlspecialchars($quote['decline_note']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php 
            endforeach; 
        else: 
        ?>
            <p class="text-gray-500">No archived quotations available.</p>
        <?php endif; ?>
    </main>

    <script>
    function showDecline(button) {
        let wrapper = button.closest(".flex");
        wrapper.querySelector(".decline-note").classList.remove("hidden");
        button.classList.add("hidden");
        wrapper.querySelector("form button[type='submit']").classList.add("hidden");
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

