<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

$message = '';
$selected_quotation_id = $_GET['quotation_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    $quotation_info = $_POST['quotation_id'];
    $predefined_messages = $_POST['messages'] ?? [];
    $custom_message = trim($_POST['custom_message']);
    
    list($user_id, $quotation_id) = explode('-', $quotation_info);

    $full_message = '';
    if (!empty($predefined_messages)) {
        $full_message .= implode("\n", $predefined_messages);
    }
    if (!empty($custom_message)) {
        $full_message .= (!empty($full_message) ? "\n\n" : '') . "Custom Note: " . $custom_message;
    }

    if (!empty($user_id) && !empty($full_message)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, status, created_at) VALUES (?, ?, 'unread', NOW())");
            if ($stmt->execute([$user_id, $full_message])) {
                $message = "<div class='alert alert-success'>Notification sent successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Failed to send notification.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Please select a quotation and enter a message.</div>";
    }
}

try {
    $stmt = $pdo->prepare("
    SELECT 
        q.id AS quotation_id,
        u.id AS user_id,
        u.full_name AS customer_name,
        q.status AS quote_status,
        p.status AS payment_status
    FROM quotations q
    JOIN job_orders jo ON q.job_order_id = jo.id
    JOIN appointments a ON jo.appointment_id = a.id
    JOIN users u ON a.user_id = u.id
    LEFT JOIN payments p ON p.quotation_id = q.id
    WHERE (q.status IN ('accepted', 'in_service') OR p.status = 'paid')
    ORDER BY q.id DESC
");
    $stmt->execute();
    $available_quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $available_quotations = [];
    $message = "<div class='alert alert-danger'>Could not fetch quotations: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification to Customer</title>
    <link rel="stylesheet" href="css/bay_manager.css">
    <style>
        .container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: .5rem; font-weight: bold; }
        select, textarea { width: 100%; padding: .75rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { resize: vertical; min-height: 100px; }
        .btn { display: inline-block; padding: .75rem 1.5rem; background-color: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; text-align: center; }
        .btn:hover { background-color: #0056b3; }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .checkbox-group label { font-weight: normal; display: block; margin-bottom: 0.5rem; }
        .checkbox-group input { margin-right: 0.5rem; }
    </style>
</head>
<body>

<div class="header">
    <h1>Send Notification to Customer</h1>
    <a href="dashboard.php" class="back-link">Back to Dashboard</a>
</div>


    <div class="main-content">
        <div class="container">
            <h1>Send Notification to Customer</h1>
            <?php echo $message; ?>
            <form action="staff_sendNotif.php" method="POST">
                <div class="form-group">
                    <label for="quotation_id">Select a Quotation</label>
                    <select name="quotation_id" id="quotation_id" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($available_quotations as $quote): 
                            $selected = ($selected_quotation_id == $quote['quotation_id']) ? 'selected' : '';
                        ?>
                            <option value="<?= $quote['user_id'] ?>-<?= $quote['quotation_id'] ?>" <?= $selected ?> >
                                Quotation #<?= htmlspecialchars($quote['quotation_id']) ?> - <?= htmlspecialchars($quote['customer_name']) ?> (<?= ucfirst($quote['quote_status']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Predefined Messages</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="messages[]" value="We are now starting the service on your vehicle."> We are now starting the service on your vehicle.</label>
                        <label><input type="checkbox" name="messages[]" value="Your vehicle service is complete and it is now ready for pickup."> Your vehicle service is complete and it is now ready for pickup.</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="custom_message">Optional Custom Message</label>
                    <textarea name="custom_message" id="custom_message" placeholder="Add any additional details here..."></textarea>
                </div>

                <button type="submit" name="send_notification" class="btn">Send Notification</button>
            </form>
        </div>
    </div>
</body>
</html>
