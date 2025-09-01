<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include __DIR__ . '/../../resources/config.php';

if (isset($_GET['id'], $_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'activate' || $action === 'deactivate') {
        $new_status = $action === 'activate' ? 'active' : 'inactive';

        $stmt = $pdo->prepare("UPDATE users SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $new_status,
            ':id' => $id
        ]);
    }
}

header("Location: maintenance.php");
exit();
