<?php
require_once 'config.php';

$filter_date = $_GET['date'] ?? '';
$filter_status = $_GET['status'] ?? 'pending'; 
$where_clauses = [];
$params = [];

if ($filter_status) {
    $where_clauses[] = "a.status = ?";
    $params[] = $filter_status;
}

if ($filter_date) {
    $where_clauses[] = "a.scheduled_date = ?";
    $params[] = $filter_date;
}

$where_sql = $where_clauses ? "WHERE " . implode(" AND ", $where_clauses) : "";

$sql = "
SELECT 
    a.id, a.scheduled_date, a.scheduled_time, a.status, a.notes,
    u.full_name, u.contact_number, s.name AS service_name, s.cost,
    v.brand, v.model, v.plate_number
FROM appointments a
JOIN users u ON a.user_id = u.id
JOIN services s ON a.service_id = s.id
JOIN vehicles v ON a.vehicle_id = v.id
$where_sql
ORDER BY a.scheduled_date, a.scheduled_time
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
    <tr>
        <form method="POST">
            <td><?= htmlspecialchars($row['full_name']) ?></td>
            <td><?= htmlspecialchars($row['brand']) ?> <?= htmlspecialchars($row['model']) ?> (<?= htmlspecialchars($row['plate_number']) ?>)</td>
            <td><?= htmlspecialchars($row['service_name']) ?> - ₱<?= number_format($row['cost'], 2) ?></td>
            <td><input type="date" name="scheduled_date" value="<?= $row['scheduled_date'] ?>" required></td>
            <td><input type="time" name="scheduled_time" value="<?= $row['scheduled_time'] ?>" required></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['notes']) ?></td>
            <td><?= htmlspecialchars($row['contact_number']) ?></td>
            <td>
                <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                <?php if ($row['status'] === 'pending'): ?>
                    <button type="submit" name="confirm">Confirm</button>
                    <button type="submit" name="decline" onclick="return confirm('Decline this appointment?')">Decline</button>
                <?php endif; ?>
                <button type="submit" name="delete" onclick="return confirm('Delete this appointment?')">Delete</button>
            </td>
        </form>
    </tr>
<?php endwhile; ?>
