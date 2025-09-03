<?php
session_start();
require_once 'config.php';
include 'header.php';


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'customer') {
    header("Location: login.php");
    exit();
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
  <title>Archived Quotations | KCS Auto Service</title>
</head>
<body class="bg-gray-100 font-sans">

  <div class="container mx-auto px-4 py-8 pt-24">
    <div class="bg-white rounded-lg shadow-lg p-6">
      <h1 class="text-3xl font-bold text-gray-800 mb-6">Archived Quotations</h1>

      <?php 
      $archivedQuotations = array_filter($allQuotations, fn($q) => $q['status'] !== 'pending');

      // Tailwind-like status badge colors
      function getQuoteStatusClasses($status) {
          switch ($status) {
              case 'accepted':
                  return 'bg-green-200 text-green-700';
              case 'declined':
                  return 'bg-red-200 text-red-700';
              case 'revised':
                  return 'bg-yellow-200 text-yellow-700';
              default:
                  return 'bg-gray-200 text-gray-600';
          }
      }

      if (!empty($archivedQuotations)): 
          foreach ($archivedQuotations as $quote): 
              $statusClasses = getQuoteStatusClasses($quote['status']);
      ?>
        <div class="border border-gray-200 rounded-lg p-4 mb-6 shadow-sm hover:shadow-md transition">
          <div class="flex justify-between items-center mb-3">
            <h3 class="text-lg font-semibold text-gray-800">
              Quotation for Job Order #<?= $quote['job_order_id'] ?>
            </h3>
            <span class="px-3 py-1 rounded-full text-xs font-medium <?= $statusClasses ?>">
              <?= ucfirst(htmlspecialchars($quote['status'])) ?>
            </span>
          </div>

          <div class="text-sm text-gray-700 space-y-1">
            <p><strong>Service:</strong> <?= htmlspecialchars($quote['service_name']) ?></p>
            <p><strong>Total Amount:</strong> ₱<?= number_format($quote['amount'], 2) ?></p>
            <?php if ($quote['status'] === 'declined' && !empty($quote['decline_note'])): ?>
              <div class="mt-2 text-red-600">
                <strong>Reason for Decline:</strong> <?= htmlspecialchars($quote['decline_note']) ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php 
          endforeach; 
      else: 
      ?>
        <p class="text-gray-500 text-center py-6">No archived quotations available.</p>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>

