<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_order_id = intval($_POST['job_order_id']);
    $quote_details = trim($_POST['quote_details']);
    $price = floatval($_POST['price']);

    $stmt = $pdo->prepare("INSERT INTO quotations (job_order_id, quote_details, price) VALUES (?, ?, ?)");
    $stmt->execute([$job_order_id, $quote_details, $price]);

    echo "<p style='color:green;'>Quotation created. Yay.</p>";
}


$jobOrders = $pdo->query("
    SELECT 
        jo.id, 
        jo.diagnosis, 
        u.full_name, 
        s.name AS service_name
    FROM job_orders jo
    JOIN appointments a ON jo.appointment_id = a.id
    JOIN users u ON a.user_id = u.id
    JOIN services s ON a.service_id = s.id
    ORDER BY jo.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<form method="POST">
    <label for="job_order_id">Job Order:</label><br>
    <select name="job_order_id" required>
        <?php foreach ($jobOrders as $order): ?>
            <option value="<?= $order['id'] ?>">
                #<?= $order['id'] ?> - <?= htmlspecialchars($order['full_name']) ?> - 
                <?= htmlspecialchars($order['service_name']) ?> (<?= htmlspecialchars($order['diagnosis']) ?>)
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="quote_details">Details:</label><br>
    <textarea name="quote_details" rows="5" required></textarea><br><br>

    <label for="price">Price (₱):</label>
    <input type="number" name="price" step="0.01" required><br><br>

    <button type="submit">Create Quotation</button>
</form>
