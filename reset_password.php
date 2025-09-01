<?php
session_start();
include('config.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if ($reset && $reset['expires'] >= date("U")) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = $_POST['password'];
            $password_confirm = $_POST['password_confirm'];

            if ($password !== $password_confirm) {
                echo "<script>alert('Passwords do not match.');</script>";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed_password, $reset['email']]);

                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$reset['email']]);

                echo "<script>alert('Password has been reset successfully.'); window.location.href = 'login.php';</script>";
                exit();
            }
        }
    } else {
        echo "<script>alert('Invalid or expired token.'); window.location.href = 'login.php';</script>";
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | KCS Auto Service</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <img src="https://i.ibb.co/mVz8zFGw/logo.png" alt="KCS Auto Service Logo">
        <h2>Reset Password</h2>
        <form class="login-form" method="POST">
            <div style="position: relative;">
                <input name="password" id="password" type="password" placeholder="New Password" required>
                <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-100%); cursor: pointer; color: #333; z-index: 100;"></i>
            </div><br>
            <div style="position: relative;">
                <input name="password_confirm" id="password_confirm" type="password" placeholder="Confirm New Password" required>
                <i class="fas fa-eye" id="togglePasswordConfirm" style="position: absolute; right: 10px; top: 50%; transform: translateY(-100%); cursor: pointer; color: #333; z-index: 100;"></i>
            </div><br>
            <button type="submit">Reset Password</button>
        </form>
    </div>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        const togglePasswordConfirm = document.querySelector('#togglePasswordConfirm');
        const passwordConfirm = document.querySelector('#password_confirm');

        togglePasswordConfirm.addEventListener('click', function (e) {
            const type = passwordConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirm.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
