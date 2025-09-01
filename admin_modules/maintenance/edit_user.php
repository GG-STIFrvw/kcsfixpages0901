<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include('../../config.php');

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: maintenance.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $confirmation = $_POST['confirmation'];

    if ($confirmation === 'CONFIRM') {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, role = ? WHERE id = ?");
        $stmt->execute([$full_name, $username, $role, $id]);
        header("Location: maintenance.php");
        exit();
    } else {
        $error = "You must type 'CONFIRM' to save changes.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="edit_user.css">
</head>
<body>

    <div class="header">
        <h1>Edit User</h1>
        <a href="maintenance.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to User Maintenance</a>
    </div>

    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="message error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h2>Edit User Details</h2>
            <form method="post">
                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" name="full_name" id="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="role">Role:</label>
                    <select name="role" id="role">
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="staff" <?= $user['role'] == 'staff' ? 'selected' : '' ?>>Staff</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="confirmation">Type "CONFIRM" to apply changes:</label>
                    <input type="text" name="confirmation" id="confirmation" required>
                </div>

                <div class="form-group">
                    <button type="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>