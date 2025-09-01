<?php
require '../../config.php';
session_start();

if (!isset($_SESSION['user']['id']) || $_SESSION['user']['role'] !== 'staff') {
    die("Unauthorized access.");
}

if (!isset($_GET['id'])) {
    die("Customer ID not specified.");
}

$customer_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $home_address = $_POST['home_address'];

    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, contact_number = ?, home_address = ? WHERE id = ?");
    $stmt->execute([$full_name, $email, $contact_number, $home_address, $customer_id]);

    header("Location: CDM.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    die("Customer not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer Information</title>
    <link rel="stylesheet" href="../../css/edit_customer.css">
</head>
<body>

    <div class="header">
        <h1>Edit Customer Information</h1>
        <a href="CDM.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Customer List</a>
    </div>

    <div class="container">
        <form method="POST">
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" name="full_name" id="full_name" value="<?= htmlspecialchars($customer['full_name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($customer['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" name="contact_number" id="phone" 
                value="<?= htmlspecialchars($customer['contact_number']) ?>">
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea name="home_address" id="address" rows="4"><?= htmlspecialchars($customer['home_address']) ?></textarea>
            </div>
            <div class="form-group">
                <button type="submit">Update</button>
            </div>
        </form>
    </div>

</body>
</html>
