<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_bay'])) {
        $name = trim($_POST['bay_name']);
        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO bays (name, status) VALUES (?, 'Available')");
            if ($stmt->execute([$name])) {
                $message = "Bay '" . htmlspecialchars($name) . "' added successfully.";
                $message_type = 'success';
            } else {
                $message = "Error adding bay.";
                $message_type = 'error';
            }
        } else {
            $message = "Bay name cannot be empty.";
            $message_type = 'error';
        }
    }

    if (isset($_POST['update_status'])) {
        $bay_id = intval($_POST['bay_id']);
        $new_status = $_POST['new_status'];
        $stmt = $pdo->prepare("UPDATE bays SET status=? WHERE id=?");
        if ($stmt->execute([$new_status, $bay_id])) {
            $message = "Bay #$bay_id status updated.";
            $message_type = 'success';
        } else {
            $message = "Error updating status.";
            $message_type = 'error';
        }
    }
}

$result = $pdo->query("SELECT id, name, status FROM bays ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bay Manager</title>
    <link rel="stylesheet" href="css/bay_manager.css">
</head>
<body>

<div class="header">
    <h1>Bay Manager</h1>
    <a href="dashboard.php" class="back-link">Back to Dashboard</a>
</div>

<div class="container">

    <?php if ($message): ?>
        <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="form-container">
        <h2>Add a New Bay</h2>
        <form class="add-form" method="POST">
            <div class="form-group">
                <label for="bay_name">Bay Name</label>
                <input type="text" id="bay_name" name="bay_name" required placeholder="e.g., Bay 1, Lube Bay">
            </div>
            <button type="submit" name="add_bay">Add Bay</button>
        </form>
    </div>

    <div class="table-container">
        <h2>Existing Bays</h2>
        <table class="bays-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Current Status</th>
                    <th style="width: 40%;">Change Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): 
                    $status_class = str_replace(' ', '-', $row['status']);
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><span class="status-<?= $status_class ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td>
                        <form class="status-form" method="POST">
                            <input type="hidden" name="bay_id" value="<?= $row['id'] ?>">
                            <select name="new_status">
                                <option value="Available" <?= $row['status'] == 'Available' ? 'selected' : '' ?>>Available</option>
                                <option value="Not Available" <?= $row['status'] == 'Not Available' ? 'selected' : '' ?>>Not Available</option>
                                <option value="Under Maintenance" <?= $row['status'] == 'Under Maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
                            </select>
                            <button type="submit" name="update_status">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>