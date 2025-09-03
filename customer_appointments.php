<?php
session_start();
include 'header.php';
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancel_id = intval($_POST['cancel_id']);

    // Allow cancel only if appointment is still pending or confirmed
    $check = $pdo->prepare("SELECT status FROM appointments WHERE id = ? AND user_id = ?");
    $check->execute([$cancel_id, $user_id]);
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if ($row && in_array($row['status'], ['pending', 'confirmed'])) {
        $cancel = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
        $cancel->execute([$cancel_id]);
    }
    header("Location: cus_appoint.php"); // edit this to correct file name
    exit();
}

// Load all appointments for this user
$stmt = $pdo->prepare("
    SELECT 
        a.id, a.scheduled_date, a.scheduled_time, a.status, a.notes,
        v.brand, v.model, v.plate_number,
        GROUP_CONCAT(s.name SEPARATOR ', ') AS service_names,
        SUM(s.cost) AS total_cost
    FROM appointments a
    JOIN vehicles v ON a.vehicle_id = v.id
    LEFT JOIN appointment_services aps ON aps.appointment_id = a.id
    LEFT JOIN services s ON aps.service_id = s.id
    WHERE a.user_id = ?
    GROUP BY a.id
    ORDER BY a.scheduled_date DESC, a.scheduled_time DESC
");
$stmt->execute([$user_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'yellow';
        case 'confirmed':
            return 'blue';
        case 'in-progress':
            return 'indigo';
        case 'completed':
            return 'green';
        case 'cancelled':
            return 'red';
        default:
            return 'gray';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<body class="bg-gray-100 font-sans">

    <div class="container mx-auto px-4 py-8 pt-24">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">My Appointments</h1>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-200 text-gray-600">
                        <tr>
                            <th class="py-3 px-6 text-left">Vehicle</th>
                            <th class="py-3 px-6 text-left">Services</th>
                            <th class="py-3 px-6 text-center">Schedule</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            <th class="py-3 px-6 text-left">Notes</th>
                            <th class="py-3 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php foreach ($appointments as $row): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6 text-left whitespace-nowrap">
                                    <div class="font-bold"><?= htmlspecialchars($row['plate_number']) ?></div>
                                    <div><?= htmlspecialchars($row['brand']) ?> <?= htmlspecialchars($row['model']) ?></div>
                                </td>
                                <td class="py-3 px-6 text-left">
                                    <div><?= htmlspecialchars($row['service_names']) ?></div>
                                    <div class="font-bold">₱<?= number_format($row['total_cost'], 2) ?></div>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <div><?= date('M d, Y', strtotime($row['scheduled_date'])) ?></div>
                                    <div><?= date('h:i A', strtotime($row['scheduled_time'])) ?></div>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <span class="bg-<?= getStatusColor($row['status']) ?>-200 text-<?= getStatusColor($row['status']) ?>-600 py-1 px-3 rounded-full text-xs">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-left"><?= $row['notes'] ? htmlspecialchars($row['notes']) : '-' ?></td>
                                <td class="py-3 px-6 text-center">
                                    <?php if (in_array($row['status'], ['pending', 'confirmed'])): ?>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?')">
                                            <input type="hidden" name="cancel_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="bg-red-500 text-white py-1 px-3 rounded-full text-xs hover:bg-red-600">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($appointments) === 0): ?>
                            <tr>
                                <td colspan="6" class="text-center py-6">No appointments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
