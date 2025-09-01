<?php
session_start();
include('config.php');

$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];

    $log_stmt = $pdo->prepare("INSERT INTO logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $log_stmt->execute([$user['id'], "User '{$user['username']}' logged out.", $ip, $user_agent]);

    session_unset();
    session_destroy();
}

header("Location: login.php");
exit();
