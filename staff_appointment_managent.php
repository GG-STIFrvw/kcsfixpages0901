<?php
session_start();
require_once 'config.php'; // Use require_once to include the database connection

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

// Fetch bays using PDO
$bay_stmt = $pdo->query("SELECT id, name, status FROM bays");
$bays = $bay_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch mechanics using PDO
$mech_stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE role = ?");
$mech_stmt->execute(['mechanic']);
$mechanics = $mech_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['appointment_id']);
    $new_date = $_POST['scheduled_date'];
    $new_time = $_POST['scheduled_time'];

    // Fetch appointment and customer details for notification
    $stmt_details = $pdo->prepare("
        SELECT 
            a.user_id, a.scheduled_date, a.scheduled_time,
            v.brand, v.model, v.plate_number,
            s.name AS service_name,
            u.full_name AS customer_name
        FROM appointments a
        JOIN users u ON a.user_id = u.id
        JOIN vehicles v ON a.vehicle_id = v.id
        JOIN services s ON a.service_id = s.id
        WHERE a.id = ?
    ");
    $stmt_details->execute([$id]);
    $appointment_details = $stmt_details->fetch(PDO::FETCH_ASSOC);

    if (isset($_POST['confirm'])) {
        $new_status = 'confirmed';
        $bay_id = $_POST['bay_id'];
        $mechanic_id = $_POST['mechanic_id'];

        $stmt = $pdo->prepare("UPDATE appointments SET scheduled_date=?, scheduled_time=?, status=?, bay_id=?, mechanic_id=? WHERE id=?");
        $stmt->execute([$new_date, $new_time, $new_status, $bay_id, $mechanic_id, $id]);

        $update_bay_stmt = $pdo->prepare("UPDATE bays SET status = 'Not Available' WHERE id = ?");
        $update_bay_stmt->execute([$bay_id]);

        // Insert notification for confirmation
        if ($appointment_details) {
            $message = "Your appointment for " . htmlspecialchars($appointment_details['brand']) . " " . htmlspecialchars($appointment_details['model']) . " (Plate: " . htmlspecialchars($appointment_details['plate_number']) . ") on " . htmlspecialchars($appointment_details['scheduled_date']) . " at " . htmlspecialchars($appointment_details['scheduled_time']) . " for " . htmlspecialchars($appointment_details['service_name']) . " has been confirmed.";
            $stmt_notif = $pdo->prepare("INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')");
            $stmt_notif->execute([$appointment_details['user_id'], $message]);
        }
    }

    if (isset($_POST['confirm_decline'])) {
        $new_status = 'declined';
        
        $reasons = [];
        if (!empty($_POST['decline_reasons'])) {
            $reasons = array_merge($reasons, $_POST['decline_reasons']);
        }
        if (!empty(trim($_POST['other_reason']))) {
            $reasons[] = trim($_POST['other_reason']);
        }

        $decline_reason = !empty($reasons) ? implode(', ', $reasons) : 'No reason provided.';

        $stmt = $pdo->prepare("UPDATE appointments SET scheduled_date=?, scheduled_time=?, status=?, notes=? WHERE id=?");
        $stmt->execute([$new_date, $new_time, $new_status, $decline_reason, $id]);

        // Insert notification for decline
        if ($appointment_details) {
            $message = "Your appointment for " . htmlspecialchars($appointment_details['brand']) . " " . htmlspecialchars($appointment_details['model']) . " (Plate: " . htmlspecialchars($appointment_details['plate_number']) . ") on " . htmlspecialchars($appointment_details['scheduled_date']) . " at " . htmlspecialchars($appointment_details['scheduled_time']) . " for " . htmlspecialchars($appointment_details['service_name']) . " has been declined. Reason: " . htmlspecialchars($decline_reason);
            $stmt_notif = $pdo->prepare("INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')");
            $stmt_notif->execute([$appointment_details['user_id'], $message]);
        }
    }

    if (isset($_POST['done'])) {
        $new_status = 'done';
        $stmt = $pdo->prepare("UPDATE appointments SET status=? WHERE id=?");
        $stmt->execute([$new_status, $id]);

        // Get the bay_id from the appointment
        $bay_id_stmt = $pdo->prepare("SELECT bay_id FROM appointments WHERE id = ?");
        $bay_id_stmt->execute([$id]);
        $appointment = $bay_id_stmt->fetch(PDO::FETCH_ASSOC);
        $bay_id = $appointment['bay_id'];

        $update_bay_stmt = $pdo->prepare("UPDATE bays SET status = 'Available' WHERE id = ?");
        $update_bay_stmt->execute([$bay_id]);
    }

    if (isset($_POST['delete'])) {
        $status_stmt = $pdo->prepare("SELECT status FROM appointments WHERE id=?");
        $status_stmt->execute([$id]);
        $appointment = $status_stmt->fetch(PDO::FETCH_ASSOC);

        if ($appointment && $appointment['status'] === 'in_progress') {
            $_SESSION['error_message'] = "Cannot delete an appointment that is 'in progress'.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM appointments WHERE id=?");
            $stmt->execute([$id]);
        }
    }
    header("Location: staff_appointment_managent.php");
    exit();
}

$filter_date = $_GET['date'] ?? '';
$filter_status = $_GET['status'] ?? 'all';
$where_clauses = [];
$params = [];

if ($filter_status !== 'all') {
    $where_clauses[] = "a.status = ?";
    $params[] = $filter_status;
}

if ($filter_date) {
    $where_clauses[] = "a.scheduled_date = ?";
    $params[] = $filter_date;
}

$where_sql = $where_clauses ? "WHERE " . implode(" AND ", $where_clauses) : "";

$sql = "
SELECT 
    a.id, a.scheduled_date, a.scheduled_time, a.status, a.notes,
    u.full_name, u.contact_number, s.name AS service_name, s.cost,
    v.brand, v.model, v.plate_number,
    b.name AS bay_name,
    m.full_name AS mechanic_name
FROM appointments a
JOIN users u ON a.user_id = u.id
JOIN services s ON a.service_id = s.id
JOIN vehicles v ON a.vehicle_id = v.id
LEFT JOIN bays b ON a.bay_id = b.id
LEFT JOIN users m ON a.mechanic_id = m.id
$where_sql
ORDER BY a.scheduled_date, a.scheduled_time
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Appointments</title>
    <link rel="stylesheet" href="css/manage.css">
</head>
<body>

<?php
if (isset($_SESSION['error_message'])) {
    echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']); // Clear the message after displaying
}
?>

<div class="header">
    <h1>Appointment Management</h1>
    <a href="dashboard.php" class="back-link">Back to Dashboard</a>
</div>

<div class="container">
    <div class="sidebar">
        <div class="filter-box">
            <h3>Filter Appointments</h3>
            <div class="status-filters">
                <a href="?status=all">All</a>
                <a href="?status=pending">Pending</a>
                <a href="?status=confirmed">Confirmed</a>
                <a href="?status=in_progress">In Progress</a>
                <a href="?status=done">Done</a>
                <a href="?status=declined">Declined</a>
            </div>
            <hr>
            <form class="date-filter" method="GET">
                <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
                <label for="date">Filter by Date:</label>
                <input type="date" id="date" name="date" value="<?= htmlspecialchars($filter_date) ?>">
                <button type="submit">Filter</button>
                <a href="?status=<?= urlencode($filter_status) ?>">Reset Date</a>
            </form>
        </div>
    </div>

    <div class="main-content">
        <div class="appointments-table-container">
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Customer & Vehicle</th>
                        <th>Service</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Assigned</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result as $row): ?>
                    <tr>
                        <form method="POST">
                            <td>
                                <div class="customer-info">
                                    <strong><?= htmlspecialchars($row['full_name']) ?></strong>
                                    <span><?= htmlspecialchars($row['contact_number']) ?></span>
                                </div>
                                <hr>
                                <div class="vehicle-info">
                                    <strong><?= htmlspecialchars($row['plate_number']) ?></strong>
                                    <span><?= htmlspecialchars($row['brand']) ?> <?= htmlspecialchars($row['model']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['service_name']) ?><br><strong>₱<?= number_format($row['cost'], 2) ?></strong></td>
                            <td>
                                <input type="date" name="scheduled_date" value="<?= $row['scheduled_date'] ?>" required>
                                <input type="time" name="scheduled_time" value="<?= $row['scheduled_time'] ?>" required>
                            </td>
                            <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                            <td>
                                <?php if ($row['status'] === 'pending'): ?>
                                    <select name="bay_id">
                                        <option value="">Select Bay...</option>
                                        <?php foreach ($bays as $bay): ?>
                                            <option value="<?= $bay['id'] ?>" <?= $bay['status'] !== 'Available' ? 'disabled' : '' ?>>
                                                <?= htmlspecialchars($bay['name']) ?> <?= $bay['status'] !== 'Available' ? '(Unavailable)' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="mechanic_id">
                                        <option value="">Select Mechanic...</option>
                                        <?php foreach ($mechanics as $mech): ?>
                                            <option value="<?= $mech['id'] ?>"><?= htmlspecialchars($mech['full_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <strong>Bay:</strong> <?= $row['bay_name'] ? htmlspecialchars($row['bay_name']) : 'N/A' ?><br>
                                    <strong>Mechanic:</strong> <?= $row['mechanic_name'] ? htmlspecialchars($row['mechanic_name']) : 'N/A' ?>
                                <?php endif; ?>
                            </td>
                            <td class="actions-cell">
                                <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                                <?php if ($row['status'] === 'pending'): ?>
                                    <div id="actions_<?= $row['id'] ?>">
                                        <button type="submit" name="confirm" class="confirm-btn">Confirm</button>
                                        <button type="button" name="decline" class="decline-btn" onclick="showDeclineReason(<?= $row['id'] ?>)">Decline</button>
                                    </div>
                                    <div id="decline_reason_container_<?= $row['id'] ?>" style="display: none;">
                                        <h4>Reason for Decline</h4>
                                        <input type="checkbox" name="decline_reasons[]" value="Service temporarily unavailable"> Service temporarily unavailable<br>
                                        <input type="checkbox" name="decline_reasons[]" value="Garage is full on the selected date"> Garage is full on the selected date<br>
                                        <input type="checkbox" name="decline_reasons[]" value="Scheduling conflict"> Scheduling conflict<br>
                                        <input type="checkbox" name="decline_reasons[]" value="Requires services we do not offer"> Requires services we do not offer<br>
                                        <input type="checkbox" name="decline_reasons[]" value="Customer has outstanding balance"> Customer has outstanding balance<br>
                                        <input type="checkbox" name="decline_reasons[]" value="Incomplete vehicle information"> Incomplete vehicle information<br>
                                        <br>
                                        <label for="other_reason_<?= $row['id'] ?>">Other reason:</label>
                                        <textarea name="other_reason" id="other_reason_<?= $row['id'] ?>" rows="3"></textarea>
                                        <br>
                                        <button type="submit" name="confirm_decline" class="decline-btn">Confirm Decline</button>
                                        <button type="button" onclick="hideDeclineReason(<?= $row['id'] ?>)">Cancel</button>
                                    </div>
                                <?php elseif ($row['status'] === 'confirmed' || $row['status'] === 'in_progress'): ?>
                                    <button type="submit" name="done" class="done-btn">Done</button>
                                <?php endif; ?>
                                <button type="submit" name="delete" class="delete-btn" onclick="return confirm('Are you sure you want to permanently delete this appointment?')">Delete</button>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showDeclineReason(id) {
    document.getElementById('actions_' + id).style.display = 'none';
    document.getElementById('decline_reason_container_' + id).style.display = 'block';
}

function hideDeclineReason(id) {
    document.getElementById('actions_' + id).style.display = 'block';
    document.getElementById('decline_reason_container_' + id).style.display = 'none';
}

document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function (e) {
        const confirmBtn = form.querySelector('button[name="confirm"]');
        if (document.activeElement === confirmBtn) {
            const baySelect = form.querySelector('select[name="bay_id"]');
            const mechSelect = form.querySelector('select[name="mechanic_id"]');
            if (!baySelect.value || !mechSelect.value) {
                alert('Please select a Bay and a Mechanic to confirm the appointment.');
                e.preventDefault();
            }
        }
    });
});
</script>

</body>
</html>