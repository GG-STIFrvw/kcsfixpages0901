<?php
session_start();
include('config.php');
require_once 'vendor/autoload.php';

use SendinBlue\Client\Configuration;
use GuzzleHttp\Client;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Model\SendSmtpEmail;
use SendinBlue\Client\Model\SendSmtpEmailSender;
use SendinBlue\Client\Model\SendSmtpEmailTo;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(50));
        $expires = date("U") + 1800; // 30 minutes

        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);

        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);

        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;

        include('API_config.php');
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', BREVO_API_KEY);
        $apiInstance = new Client();
        $transactionalEmailsApi = new TransactionalEmailsApi($apiInstance, $config);

        $sendSmtpEmail = new SendSmtpEmail();
        $sendSmtpEmail->setSender(new SendSmtpEmailSender(['name' => 'KCS Auto Service', 'email' => 'rockenrollin6767@gmail.com']));
        $sendSmtpEmail->setTo([new SendSmtpEmailTo(['email' => $user['email'], 'name' => $user['full_name']])]);
        $sendSmtpEmail->setSubject('Password Reset Request for KCS Auto Service');
        $sendSmtpEmail->setHtmlContent("<html><body><h1>Password Reset Request</h1><p>Click on the following link to reset your password: <a href='{$reset_link}'>{$reset_link}</a></p></body></html>");

        try {
            $transactionalEmailsApi->sendTransacEmail($sendSmtpEmail);
            echo "<script>alert('Password reset link has been sent to your email.');</script>";
        } catch (Exception $e) {
            error_log('Error sending password reset email: ' . $e->getMessage());
            echo "<script>alert('Could not send password reset email. Please try again later.');</script>";
        }
    } else {
        echo "<script>alert('No user found with that email address.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | KCS Auto Service</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <img src="https://i.ibb.co/mVz8zFGw/logo.png" alt="KCS Auto Service Logo">
        <h2>Forgot Password</h2>
        <form class="login-form" method="POST">
            <input name="email" type="email" placeholder="Enter your email address" required><br>
            <button type="submit">Send Password Reset Link</button>
        </form>
        <a href="login.php" class="register-link">Back to Login</a>
    </div>
</body>
</html>
