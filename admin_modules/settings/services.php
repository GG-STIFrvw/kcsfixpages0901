<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . '/../../resources/config.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = $_POST['name'];
        $desc = $_POST['description'];
        $cost = floatval($_POST['cost']);
        $status = $_POST['status'];
        try {
            $stmt = $pdo->prepare("INSERT INTO services (name, description, cost, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $desc, $cost, $status]);
            $message = "Service added successfully.";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Error adding service: " . $e->getMessage();
            $message_type = "error";
        }
    } elseif (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $name = $_POST['name'];
        $desc = $_POST['description'];
        $cost = floatval($_POST['cost']);
        $status = $_POST['status'];
        try {
            $stmt = $pdo->prepare("UPDATE services SET name=?, description=?, cost=?, status=? WHERE id=?");
            $stmt->execute([$name, $desc, $cost, $status, $id]);
            $message = "Service updated successfully.";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Error updating service: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Service deleted successfully.";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Error deleting service: " . $e->getMessage();
        $message_type = "error";
    }
    // Redirect to remove GET parameters after action
    header("Location: services.php");
    exit();
}

$edit_service = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $edit_service = $stmt->fetch(PDO::FETCH_ASSOC);
}

$services_stmt = $pdo->query("SELECT * FROM services");
$services = $services_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services</title>
    <link rel="stylesheet" href="services.css">
</head>
<body>

    <div class="header">
        <h1>Manage Services</h1>
        <a href="../../dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h2><?= $edit_service ? 'Edit Service' : 'Add New Service' ?></h2>
            <form method="POST">
                <?php if ($edit_service): ?>
                    <input type="hidden" name="id" value="<?= $edit_service['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Service Name:</label>
                    <input type="text" name="name" id="name" value="<?= htmlspecialchars($edit_service['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <input type="text" name="description" id="description" value="<?= htmlspecialchars($edit_service['description'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="cost">Cost (₱):</label>
                    <input type="number" name="cost" id="cost" step="0.01" value="<?= htmlspecialchars($edit_service['cost'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                        <option value="Available" <?= ($edit_service['status'] ?? '') === 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Not Available" <?= ($edit_service['status'] ?? '') === 'Not Available' ? 'selected' : '' ?>>Not Available</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" name="<?= $edit_service ? 'update' : 'add' ?>">
                        <?= $edit_service ? 'Update Service' : 'Add Service' ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="form-section">
            <h2>Existing Services</h2>
            <?php if (!empty($services)): ?>
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Cost</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $svc): ?>
                            <tr>
                                <td><?= $svc['id'] ?></td>
                                <td><?= htmlspecialchars($svc['name']) ?></td>
                                <td><?= htmlspecialchars($svc['description']) ?></td>
                                <td>₱<?= number_format($svc['cost'], 2) ?></td>
                                <td><?= htmlspecialchars($svc['status']) ?></td>
                                <td class="action-buttons">
                                    <a href="?edit=<?= $svc['id'] ?>" class="edit-btn">Edit</a>
                                    <br><br>
                                    <a href="?delete=<?= $svc['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this service?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No services added yet.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>