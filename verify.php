<?php
require 'config.php';

$message = "";
$message_type = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        if ($user['email_verified']) {
            $message = "✅ Email already verified.";
            $message_type = "success";
        } else {
            $update = $pdo->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?");
            $update->execute([$user['id']]);
            $message = "✅ Email verified! You can now <a href='login.php'>log in</a>.";
            $message_type = "success";
        }
    } else {
        $message = "❌ Invalid or expired token.";
        $message_type = "error";
    }
} else {
    $message = "❌ No token provided.";
    $message_type = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification | KCS Auto Service</title>
    <link rel="stylesheet" href="css/verify.css">
</head>
<body>
    <div class="verify-container">
        <img src="https://i.ibb.co/mVz8zFGw/logo.png" alt="KCS Auto Service Logo">
        <h2>Email Verification</h2>
        <div class="message <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    </div>
</body>
</html>
