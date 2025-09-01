<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quote_id = intval($_POST['quote_id']);
    $status = $_POST['status'];
    $note = $_POST['note'] ?? '';

    // Update the quotation status
    $stmt = $pdo->prepare("UPDATE quotations SET status = ?, decline_note = ? WHERE id = ?");
    $stmt->execute([$status, $note, $quote_id]);

    if ($status === 'accepted') {
        // Get the job_order_id from the quotation
        $stmt = $pdo->prepare("SELECT job_order_id FROM quotations WHERE id = ?");
        $stmt->execute([$quote_id]);
        $quotation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($quotation) {
            $job_order_id = $quotation['job_order_id'];

            // Update the job_order status to 'in_progress'
            $stmt = $pdo->prepare("UPDATE job_orders SET status = 'in_progress' WHERE id = ?");
            $stmt->execute([$job_order_id]);

            // Also update the corresponding appointment's status to 'in_progress' for consistency
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'in_progress' WHERE id = (SELECT appointment_id FROM job_orders WHERE id = ?)");
            $stmt->execute([$job_order_id]);
        }
    }

    // Redirect back to the admin view page
    header("Location: staff_view_quotation.php"); 
    exit();
}
?>