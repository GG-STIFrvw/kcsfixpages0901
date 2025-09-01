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


<!-- Push content down below fixed header -->
<div class="pt-[120px] px-6 max-w-4xl mx-auto">

  <h2 class="text-2xl font-semibold mb-6">Archived Quotations</h2>

  <?php 
  $archivedQuotations = array_filter($allQuotations, fn($q) => $q['status'] !== 'pending');
  if (!empty($archivedQuotations)): 
      foreach ($archivedQuotations as $quote):
          $statusClass = 'status-' . htmlspecialchars($quote['status']);
  ?>
      <div class="quote-card <?= $statusClass ?> mb-6 border border-gray-200 rounded-lg p-4 shadow-sm">
          <div class="quote-content">
              <div class="quote-header flex justify-between items-center mb-2">
                  <h3 class="text-lg font-semibold">Quotation for Job Order #<?= $quote['job_order_id'] ?></h3>
                  <span class="quote-status <?= $statusClass ?> text-sm px-3 py-1 bg-gray-100 rounded-full">
                      <?= ucfirst(htmlspecialchars($quote['status'])) ?>
                  </span>
              </div>
              <div class="quote-body text-sm space-y-1">
                  <p><strong>Service:</strong> <?= htmlspecialchars($quote['service_name']) ?></p>
                  <p><strong>Total Amount:</strong> ₱<?= number_format($quote['amount'], 2) ?></p>
                  <?php if ($quote['status'] === 'declined' && !empty($quote['decline_note'])): ?>
                      <div class="decline-reason text-red-600 mt-2">
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
      <p class="text-gray-500">No archived quotations available.</p>
  <?php endif; ?>
</div>
