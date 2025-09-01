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
    header("Location: customer_appointments.php");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Appointments</title>
    <link rel="stylesheet" href="css/my_appointments.css">
</head>
<body>

    <br><br><br><br>

    <div class="container">
        <div class="main-content">
            <div class="appointments-table-container">
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Services</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($row['plate_number']) ?></strong><br>
                                    <?= htmlspecialchars($row['brand']) ?> <?= htmlspecialchars($row['model']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['service_names']) ?><br>
                                    <strong>₱<?= number_format($row['total_cost'], 2) ?></strong>
                                </td>
                                <td><?= $row['scheduled_date'] ?> @ <?= $row['scheduled_time'] ?></td>
                                <td><?= ucfirst($row['status']) ?></td>
                                <td><?= $row['notes'] ? htmlspecialchars($row['notes']) : '-' ?></td>
                                <td>
                                    <?php if (in_array($row['status'], ['pending', 'confirmed'])): ?>
                                        <form method="POST">
                                            <input type="hidden" name="cancel_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="cancel-btn" onclick="return confirm('Cancel this appointment?')">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($appointments) === 0): ?>
                            <tr><td colspan="6" style="text-align:center;">No appointments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
