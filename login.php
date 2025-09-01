<?php 
session_start();
include('config.php');

$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

$rateLimitWindow = 5 * 60; 
$maxAttempts = 5;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check for too many recent failed attempts
    $rate_stmt = $pdo->prepare("
        SELECT COUNT(*) FROM logs
        WHERE action LIKE 'Failed login attempt%' AND ip_address = ? AND log_time >= NOW() - INTERVAL ? SECOND
    ");
    $rate_stmt->execute([$ip, $rateLimitWindow]);
    $failedAttempts = $rate_stmt->fetchColumn();

    if ($failedAttempts >= $maxAttempts) {
        echo "<script>alert('Too many failed login attempts. Try again later.');</script>";
        exit();
    }

    // Find user by username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Check if email is verified
        if (!$user['email_verified']) {
            echo "<script>alert('Please verify your email before logging in.');</script>";
            $log_stmt = $pdo->prepare("INSERT INTO logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
            $log_stmt->execute([$user['id'], "Login blocked - unverified email for '{$username}'.", $ip, $user_agent]);
            exit();
        }

        // Check if account is deactivated
        if ($user['status'] === 'inactive') {
            echo "<script>alert('Your account has been deactivated by the admin.');</script>";
            $log_stmt = $pdo->prepare("INSERT INTO logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
            $log_stmt->execute([$user['id'], "Login blocked for deactivated user '{$username}'.", $ip, $user_agent]);
            exit();
        }

        // OTP check for specific roles
        //$privileged_roles = ['admin', 'staff', 'inventory_manager'];
        $privileged_roles = ['a', 's', 'i'];
        if (in_array($user['role'], $privileged_roles)) {
            // Generate and send OTP
            $otp = rand(100000, 999999);
            $_SESSION['otp_code'] = $otp;
            $_SESSION['otp_user_id'] = $user['id'];

            // Send OTP via Email using Brevo
            require_once 'vendor/autoload.php';
            include('API_config.php');

            $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', BREVO_API_KEY);
            $apiInstance = new GuzzleHttp\Client();
            $transactionalEmailsApi = new SendinBlue\Client\Api\TransactionalEmailsApi($apiInstance, $config);

            $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail();
            $sendSmtpEmail->setSender(new \SendinBlue\Client\Model\SendSmtpEmailSender(['name' => 'KCS Auto Service', 'email' => 'rockenrollin6767@gmail.com']));
            $sendSmtpEmail->setTo([new \SendinBlue\Client\Model\SendSmtpEmailTo(['email' => $user['email'], 'name' => $user['full_name']])]);
            $sendSmtpEmail->setSubject('Your OTP for KCS Auto Service');
            $sendSmtpEmail->setHtmlContent("<html><body><h1>Your OTP</h1><p>Your OTP for KCS Auto Service is: <strong>{$otp}</strong></p></body></html>");

            try {
                $transactionalEmailsApi->sendTransacEmail($sendSmtpEmail);
            } catch (Exception $e) {
                // Log error but don't expose to user
                error_log('Error sending OTP email: ' . $e->getMessage());
            }

            header("Location: verify_otp.php");
            exit();

        } else {
            // Standard user login
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'role' => $user['role'],
                'profile_picture' => $user['profile_picture'],
                'status' => $user['status']
            ];

            $log_stmt = $pdo->prepare("INSERT INTO logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
            $log_stmt->execute([$user['id'], "User '{$username}' logged in.", $ip, $user_agent]);

            // Redirect to role-based dashboard
            if ($user['role'] == 'customer') {
                header("Location: index.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        }

    } else {
        // Login failed
        $log_stmt = $pdo->prepare("INSERT INTO logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
        $log_stmt->execute([null, "Failed login attempt for username '{$username}'.", $ip, $user_agent]);

        echo "<script>alert('Login failed.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | KCS Auto Service</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <img src="https://i.ibb.co/mVz8zFGw/logo.png" alt="KCS Auto Service Logo">
        <h2>Log In</h2>
        <form class="login-form" method="POST">
            <input name="username" placeholder="Username" required><br>
            <div style="position: relative;">
                <input name="password" id="password" type="password" placeholder="Password" required>
                <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-100%); cursor: pointer; color: #333; z-index: 100;"></i>
            </div><br>
            <button type="submit">Login</button>
        </form>
        <a href="register.php" class="register-link">Don't have an account? Register</a>
        <a href="forgot_password.php" class="forgot-password-link">Forgot Password?</a>
    </div>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>