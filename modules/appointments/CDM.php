<?php
require '../../config.php';
session_start();

if (!isset($_SESSION['user']['id']) || $_SESSION['user']['role'] !== 'staff') {
    die("Unauthorized access.");
}

// Fetch all customers
$stmt = $pdo->query("SELECT id, full_name, email, contact_number, home_address, created_at FROM users WHERE role = 'customer' ORDER BY full_name");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Data Management</title>
    <link rel="stylesheet" href="../../css/cdm.css">
</head>
<body>
    

    <div class="header">
        <h1>Customer Data Management</h1>
        <a href="../../dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['error_message'])) : ?>
            <div class="error-message">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="table-section">
            <h2>All Customers</h2>
            <?php if (!empty($customers)) : ?>
                <table class="customer-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Account Creation Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer) : ?>
                            <tr>
                                <td><?= htmlspecialchars($customer['full_name']) ?></td>
                                <td><?= htmlspecialchars($customer['email']) ?></td>
                                <td><?= htmlspecialchars($customer['contact_number']) ?></td>
                                <td><?= htmlspecialchars($customer['home_address']) ?></td>
                                <td><?= htmlspecialchars($customer['created_at']) ?></td>
                                <td class="actions">
                                    <a href="view_customer.php?id=<?= $customer['id'] ?>" class="action-btn view-btn">View</a>
                                    <a href="edit_customer.php?id=<?= $customer['id'] ?>" class="action-btn edit-btn">Edit</a>
                                    <a href="delete_customer.php?id=<?= $customer['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this customer?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No customers found.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
