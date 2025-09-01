<?php 
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include('../../config.php');

$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Maintenance</title>
    <link rel="stylesheet" href="maintenance.css">
</head>
<body>

    <div class="header">
        <h1>User Maintenance</h1>
        <a href="../../dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="container">
        <a href="add_user.php" class="add-user-button">Add New User</a>

        <table class="users-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= htmlspecialchars($user['status']) ?></td>
                    <td class="action-buttons">
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="edit-btn">Edit</a>
                        <?php if ($user['status'] === 'active'): ?>
                            <a href="#" onclick="return confirmStatusChange('toggle_status.php?id=<?= $user['id'] ?>&action=deactivate', 'deactivate');" class="deactivate-btn">Deactivate</a>
                        <?php else: ?>
                            <a href="#" onclick="return confirmStatusChange('toggle_status.php?id=<?= $user['id'] ?>&action=activate', 'activate');" class="activate-btn">Activate</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    function confirmStatusChange(url, action) {
        const input = prompt("Type 'CONFIRM' to " + action + " this user:");
        if (input === "CONFIRM") {
            window.location.href = url;
        } else {
            alert("Confirmation failed. Action canceled.");
        }
        return false;
    }
    </script>

</body>
</html>