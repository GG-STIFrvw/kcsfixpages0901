<?php
session_start();
include('config.php');

// Redirect if OTP session variables aren't set
if (!isset($_SESSION['otp_code']) || !isset($_SESSION['otp_user_id'])) {
    header("Location: login.php");
    exit();
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submitted_otp = $_POST['otp'];

    if ($submitted_otp == $_SESSION['otp_code']) {
        // OTP is correct, fetch user details and create session
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['otp_user_id']]);
        $user = $stmt->fetch();

        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
            'profile_picture' => $user['profile_picture'],
            'status' => $user['status']
        ];

        // Log successful OTP verification
        $log_stmt = $pdo->prepare("INSERT INTO logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
        $log_stmt->execute([$user['id'], "User '{$user['username']}' completed OTP verification.", $ip, $user_agent]);

        // Clear OTP session variables
        unset($_SESSION['otp_code']);
        unset($_SESSION['otp_user_id']);

        // Redirect to the main dashboard
        header("Location: dashboard.php");
        exit();

    } else {
        // Incorrect OTP
        echo "<script>alert('Invalid OTP. Please try again.');</script>";
        $log_stmt = $pdo->prepare("INSERT INTO logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
        $log_stmt->execute([$_SESSION['otp_user_id'], "Failed OTP verification attempt.", $ip, $user_agent]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP | KCS Auto Service</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <img src="https://i.ibb.co/mVz8zFGw/logo.png" alt="KCS Auto Service Logo">
        <h2>Enter OTP</h2>
        <p>An OTP has been sent to your registered email address.</p>
        <form class="login-form" method="POST">
            <input name="otp" placeholder="Enter 6-Digit OTP" required maxlength="6"><br>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>
