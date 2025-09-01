<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/../../../resources/config.php';
require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$userFilter = $_GET['user'] ?? '';
$ipFilter = $_GET['ip'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

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

$query = "
    SELECT logs.log_time, users.full_name, logs.action, logs.ip_address, logs.user_agent
    FROM logs
    LEFT JOIN users ON logs.user_id = users.id
    $where
    ORDER BY logs.log_time DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray(['Timestamp', 'User', 'Action', 'IP Address', 'User Agent'], null, 'A1');

$rowNum = 2;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sheet->fromArray([
        $row['log_time'],
        $row['full_name'] ?? 'Unknown',
        $row['action'],
        $row['ip_address'],
        $row['user_agent']
    ], null, "A$rowNum");
    $rowNum++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="log_history.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
