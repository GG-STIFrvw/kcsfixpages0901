<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

require_once 'config.php'; 

$message = '';
$message_type = '';
$image_upload_enabled = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $diagnosis = trim($_POST['diagnosis']);
    $mechanic_id = $_SESSION['user']['id'];
    $status = 'pending';
    $image_path = null;

    if ($image_upload_enabled && isset($_FILES['inspection_image']) && $_FILES['inspection_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $filename = basename($_FILES['inspection_image']['name']);
        $target_file = $upload_dir . time() . "_" . $filename;
        if (move_uploaded_file($_FILES['inspection_image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO job_orders (appointment_id, mechanic_id, diagnosis, status, image_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$appointment_id, $mechanic_id, $diagnosis, $status, $image_path]);

        $pdo->prepare("UPDATE appointments SET status = 'in_progress' WHERE id = ?")->execute([$appointment_id]);

        $message = "Job Order created and appointment status updated to 'In Progress'!";
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = 'error';
    }
}

$stmt = $pdo->query("
    SELECT a.id AS appointment_id, u.full_name, v.brand, v.model, v.plate_number, s.name AS service_name
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN vehicles v ON a.vehicle_id = v.id
    JOIN services s ON a.service_id = s.id
    WHERE a.status = 'confirmed'
    ORDER BY a.scheduled_date, a.scheduled_time
");
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$jobOrders = $pdo->query("
    SELECT jo.id, jo.diagnosis, jo.status, jo.image_path, u.full_name, v.plate_number
    FROM job_orders jo
    JOIN appointments a ON jo.appointment_id = a.id
    JOIN users u ON a.user_id = u.id
    JOIN vehicles v ON a.vehicle_id = v.id
    ORDER BY jo.id DESC LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Order Generation</title>
    <link rel="stylesheet" href="css/jog.css">
</head>
<body>

<div class="header">
    <h1>Job Order Generation</h1>
    <a href="dashboard.php" class="back-link">Back to Dashboard</a>
</div>

<div class="container">
    <div class="form-container">
        <h2>Create New Job Order</h2>
        
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form class="job-order-form" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="appointment_id">Select Confirmed Appointment:</label>
                <select name="appointment_id" id="appointment_id" required>
                    <option value="" disabled selected>-- Choose an appointment --</option>
                    <?php foreach ($appointments as $row): ?>
                        <option value="<?= $row['appointment_id'] ?>">
                            <?= htmlspecialchars($row['plate_number']) ?> - <?= htmlspecialchars($row['full_name']) ?> (<?= htmlspecialchars($row['service_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="diagnosis">Mechanic's Diagnosis:</label>
                <textarea name="diagnosis" id="diagnosis" rows="6" required></textarea>
            </div>

            <?php if ($image_upload_enabled): ?>
            <div class="form-group">
                <label for="inspection_image">Upload Inspection Photo (optional):</label>
                <input type="file" name="inspection_image" id="inspection_image" accept="image/*">
            </div>
            <?php endif; ?>

            <button type="submit">Generate Job Order</button>
        </form>
    </div>

    <div class="table-container">
        <h2>Recent Job Orders</h2>
        <table class="job-orders-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Plate No.</th>
                    <th>Diagnosis</th>
                    <th>Status</th>
                    <th>Image</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobOrders)): ?>
                    <tr><td colspan="6" style="text-align:center;">No job orders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($jobOrders as $jo): ?>
                    <tr>
                        <td><?= $jo['id'] ?></td>
                        <td><?= htmlspecialchars($jo['full_name']) ?></td>
                        <td><?= htmlspecialchars($jo['plate_number']) ?></td>
                        <td><?= htmlspecialchars($jo['diagnosis']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($jo['status'])) ?></td>
                        <td>
                            <?php if ($jo['image_path']): ?>
                                <a href="<?= htmlspecialchars($jo['image_path']) ?>" target="_blank">
                                    <img src="<?= htmlspecialchars($jo['image_path']) ?>" alt="Inspection Photo">
                                </a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>