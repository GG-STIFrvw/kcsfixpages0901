<?php
include('../../config.php');

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $role = $_POST['role'];

    if (!in_array($role, ['admin', 'customer', 'staff', 'inventory_manager', 'mechanic'])) {
        $message = "Invalid role selected.";
        $message_type = "error";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $password, $email, $full_name, $role]);
            $message = "Account created successfully.";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Error creating account: " . $e->getMessage();
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link rel="stylesheet" href="add_user.css">
</head>
<body>

    <div class="header">
        <h1>Add New User</h1>
        <a href="maintenance.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to User Maintenance</a>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h2>User Details</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" name="username" id="username" placeholder="Enter username" required>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" name="full_name" id="full_name" placeholder="Enter full name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" placeholder="Enter email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" placeholder="Enter password" required>
                </div>

                <div class="form-group">
                    <label for="role">Role:</label>
                    <select name="role" id="role" required>
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                        <option value="inventory_manager">Inventory Manager</option>
                        <option value="mechanic">Mechanic</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit">Register User</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>