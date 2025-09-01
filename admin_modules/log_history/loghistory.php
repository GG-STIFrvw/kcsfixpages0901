<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once "../../config.php";

function parseUserAgent($ua) {
    if (stripos($ua, 'Chrome') !== false && stripos($ua, 'Edge') === false) return 'Chrome';
    if (stripos($ua, 'Firefox') !== false) return 'Firefox';
    if (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) return 'Safari';
    if (stripos($ua, 'Edge') !== false) return 'Edge';
    return 'Other';
}

function getRowClass($action) {
    if (stripos($action, 'failed') !== false) return 'failed-log';
    if (stripos($action, 'logged out') !== false) return 'logged-out-log';
    return '';
}

$userFilter = $_GET['user'] ?? '';
$ipFilter = $_GET['ip'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = "WHERE 1 = 1";
$params = [];

if ($userFilter) {
    $where .= " AND users.full_name LIKE ?";
    $params[] = "%$userFilter%";
}
if ($ipFilter) {
    $where .= " AND logs.ip_address LIKE ?";
    $params[] = "%$ipFilter%";
}
if ($startDate) {
    $where .= " AND logs.log_time >= ?";
    $params[] = "$startDate 00:00:00";
}
if ($endDate) {
    $where .= " AND logs.log_time <= ?";
    $params[] = "$endDate 23:59:59";
}

$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM logs
    LEFT JOIN users ON logs.user_id = users.id
    $where
");
$countStmt->execute($params);
$totalLogs = $countStmt->fetchColumn();
$totalPages = ceil($totalLogs / $limit);

$query = "
    SELECT logs.log_time, users.full_name, logs.action, logs.ip_address, logs.user_agent
    FROM logs
    LEFT JOIN users ON logs.user_id = users.id
    $where
    ORDER BY logs.log_time DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log History</title>
    <link rel="stylesheet" href="loghistory.css">
</head>
<body>

    <div class="header">
        <h1>System Log History</h1>
        <a href="../../dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="container">
        <form method="GET" class="filter-form">
            <input type="text" name="user" placeholder="Search user" value="<?= htmlspecialchars($userFilter) ?>">
            <input type="text" name="ip" placeholder="Search IP" value="<?= htmlspecialchars($ipFilter) ?>">
            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
            <button type="submit">Filter</button>
            <a href="loghistory.php" class="reset-link">Reset</a>
            <button type="submit" formaction="export_logs_excel.php" formmethod="get" class="export-button">Export Excel</button>
        </form>

        <table class="logs-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>IP Address</th>
                    <th>Device/Browser</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr class="<?= getRowClass($row['action']) ?>">
                        <td><?= htmlspecialchars($row['log_time']) ?></td>
                        <td><?= htmlspecialchars($row['full_name'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($row['action']) ?></td>
                        <td><?= htmlspecialchars($row['ip_address'] ?? 'N/A') ?></td>
                        <td><?= parseUserAgent($row['user_agent'] ?? '') ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                Pages:
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                       class="<?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>