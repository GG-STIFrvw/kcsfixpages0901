<?php
require 'config.php';
require 'vendor/autoload.php'; // Brevo SDK

use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client;

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'];
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $role = 'customer';
    $token = bin2hex(random_bytes(16)); // email verification token

    if (empty($username) || empty($full_name) || empty($email) || empty($password) || empty($contact_number)) {
        $message = "<div class='message error'>Please fill in all fields.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='message error'>Invalid email format.</div>";
    } elseif (strlen($password) < 8 || !preg_match("/[a-z]/", $password) || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password) || !preg_match("/[^\w]/", $password)) {
        $message = "<div class='message error'>Password must be at least 8 characters long and contain at least one lowercase letter, one uppercase letter, one number, and one special character.</div>";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        $existing_user = $check->fetch();

        if ($existing_user) {
            $message = "<div class='message error'>Username or email already taken.</div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, contact_number, role, verification_token, email_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");

            if ($stmt->execute([$username, $hashed_password, $email, $full_name, $contact_number, $role, $token])) {
                // Brevo email setup
                $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', 'xkeysib-PASTE_YOUR_REAL_API_KEY_HERE');
                $apiInstance = new TransactionalEmailsApi(new Client(), $config);

                $verify_link = "http://localhost/kcs-ADMINOTP/verify.php?token=$token";
                $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
                    'subject' => 'Verify Your Email - KCS Auto Service',
                    'sender' => ['name' => 'KCS Auto Service', 'email' => 'rockenrollin6767@gmail.com'], // Must be a verified Brevo sender
                    'to' => [['email' => $email, 'name' => $full_name]],
                    'htmlContent' => "
                        <html>
                        <body>
                            <p>Hi $full_name,</p>
                            <p>Please click the link below to verify your email address:</p>
                            <a href='$verify_link'>Verify Your Email</a>
                            <p>If the link above does not work, copy and paste this URL into your browser:</p>
                            <p>$verify_link</p>
                            <p>Thank you!</p>
                        </body>
                        </html>"
                ]);

                try {
                    $apiInstance->sendTransacEmail($sendSmtpEmail);
                    echo "<script>alert('Account created! Please check your email to verify your account.'); window.location.href='login.php';</script>";
                    exit();
                } catch (Exception $e) {
                    $message = "<div class='message error'>Email sending failed: " . $e->getMessage() . "</div>";
                }
            } else {
                $message = "<div class='message error'>An error occurred. Please try again.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | KCS Auto Service</title>
    <link rel="stylesheet" href="css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="register-container">
        <img src="https://i.ibb.co/mVz8zFGw/logo.png" alt="KCS Auto Service Logo">
        <h2>Create Your Account</h2>
        <?= $message ?>
        <form class="register-form" method="POST">
            <div class="form-group">
                <input name="full_name" placeholder="Full Name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <input name="username" placeholder="Username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <input name="email" type="email" placeholder="Email Address" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <input name="contact_number" placeholder="Contact Number" required value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>">
            </div>
            <div class="form-group" style="position: relative;">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #333; z-index: 100;"></i>
            </div>
            <div class="form-group terms">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                    I agree to the <a style="text-decoration:none" href="terms-and-conditions.php" target="_blank">Terms and Conditions</a>.
                </label>
            </div>
            <button type="submit">Register</button>
        </form>
        <a href="login.php" class="login-link">Already have an account? Log In</a>
    </div>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
