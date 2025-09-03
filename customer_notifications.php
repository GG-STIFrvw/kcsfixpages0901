<?php
session_start();
require_once 'config.php';
include 'header.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user']['id'];

// Fetch custom messages from staff before marking them as read
$messagesStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$messagesStmt->execute([$userId]);
$customMessages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);

// Mark notifications as read
$updateStmt = $pdo->prepare("UPDATE notifications SET status = 'read' WHERE user_id = ? AND status = 'unread'");
$updateStmt->execute([$userId]);

// Fetch confirmed or declined appointments and related quotations
$stmt = $pdo->prepare("
    SELECT 
        a.id, a.scheduled_date, a.scheduled_time, a.status, a.notes,
        v.brand, v.model, v.plate_number,
        s.name AS service_names,
        q.id AS quotation_id, q.amount AS quotation_amount, q.status AS quotation_status
    FROM appointments a
    JOIN vehicles v ON a.vehicle_id = v.id
    JOIN services s ON a.service_id = s.id
    LEFT JOIN job_orders jo ON a.id = jo.appointment_id
    LEFT JOIN quotations q ON jo.id = q.job_order_id
    WHERE a.user_id = ? AND (a.status IN ('confirmed', 'declined', 'completed', 'done') OR q.id IS NOT NULL)
    GROUP BY a.id, q.id
    ORDER BY a.scheduled_date DESC, a.scheduled_time DESC
");
$stmt->execute([$userId]);
$bookingStatuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-6xl mx-auto p-6 pt-24">
    <div class="bg-white shadow-lg rounded-2xl p-6 space-y-12">
        
        <!-- Custom Messages from Staff -->
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Notifications</h1>
            <h2 class="text-2xl font-semibold mb-6 text-gray-800">Messages from Staff</h2>
            <?php if (!empty($customMessages)): ?>
                <div class="border border-gray-200 rounded-lg bg-gray-50 divide-y divide-gray-200">
                    <?php foreach ($customMessages as $msg): ?>
                        <div class="p-4">
                            <p class="text-gray-800"><?= htmlspecialchars($msg['message']) ?></p>
                            <small class="text-sm text-gray-500">
                                <?= date("F j, Y, g:i a", strtotime($msg['created_at'])) ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-500 py-4">
                    You have no new messages from staff.
                </p>
            <?php endif; ?>
        </div>

        <!-- Booking Status Updates -->
        <div>
            <h2 class="text-2xl font-semibold mb-6 text-gray-800">Your Booking Status Updates</h2>
            <?php if (!empty($bookingStatuses)): ?>
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Appointment ID</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Vehicle</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Services</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Scheduled</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($bookingStatuses as $booking): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-gray-700"><?= htmlspecialchars($booking['id']) ?></td>
                                    <td class="px-4 py-2 text-gray-700">
                                        <?= htmlspecialchars($booking['brand']) ?> 
                                        <?= htmlspecialchars($booking['model']) ?> 
                                        (<?= htmlspecialchars($booking['plate_number']) ?>)
                                    </td>
                                    <td class="px-4 py-2 text-gray-700">
                                        <?= htmlspecialchars($booking['service_names']) ?: 'N/A' ?>
                                    </td>
                                    <td class="px-4 py-2 text-gray-700">
                                        <?= htmlspecialchars($booking['scheduled_date']) ?> 
                                        at <?= htmlspecialchars($booking['scheduled_time']) ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <?php
                                            $status_color = 'text-gray-700';
                                            if ($booking['status'] == 'confirmed') {
                                                $status_color = 'text-green-600 font-bold';
                                            } elseif ($booking['status'] == 'declined') {
                                                $status_color = 'text-red-600 font-bold';
                                            } elseif (in_array($booking['status'], ['completed', 'done'])) {
                                                $status_color = 'text-blue-600 font-bold';
                                            }
                                        ?>
                                        <span class="<?= $status_color ?>">
                                            <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-gray-700">
                                        <?php if (!empty($booking['quotation_id'])): ?>
                                            <div class="mb-2">
                                                <p><strong>Quotation Received:</strong></p>
                                                <p><strong>Amount:</strong> ₱<?= htmlspecialchars(number_format($booking['quotation_amount'], 2)) ?></p>
                                                <p><strong>Status:</strong> <?= ucfirst(htmlspecialchars($booking['quotation_status'])) ?></p>
                                                <a href="customer_view_quote.php" class="text-blue-600 font-semibold hover:underline">
                                                    View Details
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($booking['notes'])): ?>
                                            <p><strong>Notes:</strong> <?= htmlspecialchars($booking['notes']) ?></p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-500 py-4">
                    No confirmed or declined booking updates at this time.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

