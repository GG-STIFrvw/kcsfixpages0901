<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

try {
    $stmt = $pdo->query("
        SELECT 
            jo.id AS job_order_id, jo.diagnosis, jo.status, jo.image_path,
            u.full_name AS customer_name, v.brand, v.model, v.plate_number,
            s.name AS service_name, a.scheduled_date, a.scheduled_time
        FROM job_orders jo
        JOIN appointments a ON jo.appointment_id = a.id
        JOIN users u ON a.user_id = u.id
        JOIN vehicles v ON a.vehicle_id = v.id
        JOIN services s ON a.service_id = s.id
        ORDER BY jo.id DESC
    ");
    $jobOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // It's better to handle this gracefully in the UI
    $error_message = "Error fetching job orders: " . $e->getMessage();
    $jobOrders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Job Orders</title>
    <link rel="stylesheet" href="css/all_job_orders.css">
</head>
<body>

<div class="header">
    <h1>All Job Orders</h1>
    <a href="dashboard.php" class="back-link">Back to Dashboard</a>
</div>

<div class="container">
    <div class="table-container">
        <div class="table-header">
            <h2>Job Order History & Progress</h2>
            <div class="filter-controls">
                <input type="text" id="searchInput" placeholder="Search by name, plate, etc.">
                <select id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <p style="color:red;"><?= htmlspecialchars($error_message) ?></p>
        <?php else: ?>
            <table class="job-orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Service</th>
                        <th>Scheduled</th>
                        <th>Diagnosis</th>
                        <th>Status</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody id="jobOrdersTableBody">
                    <?php if (empty($jobOrders)): ?>
                        <tr class="no-results-row"><td colspan="8">No job orders found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($jobOrders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['job_order_id']) ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= htmlspecialchars($order['brand']) ?> <?= htmlspecialchars($order['model']) ?> (<strong><?= htmlspecialchars($order['plate_number']) ?></strong>)</td>
                                <td><?= htmlspecialchars($order['service_name']) ?></td>
                                <td><?= htmlspecialchars($order['scheduled_date']) ?> <br> <?= htmlspecialchars($order['scheduled_time']) ?></td>
                                <td><?= nl2br(htmlspecialchars($order['diagnosis'])) ?></td>
                                <td><span class="status-<?= str_replace(' ', '_', strtolower($order['status'])) ?>"><?= htmlspecialchars(ucfirst($order['status'])) ?></span></td>
                                <td>
                                    <?php if ($order['image_path']): ?>
                                        <a href="<?= htmlspecialchars($order['image_path']) ?>" target="_blank">
                                            <img src="<?= htmlspecialchars($order['image_path']) ?>" alt="Inspection Photo">
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr class="no-results-row" style="display: none;"><td colspan="8">No job orders match your search.</td></tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.getElementById('jobOrdersTableBody');
    const rows = tableBody.getElementsByTagName('tr');
    const noResultsRow = tableBody.querySelector('.no-results-row');

    function filterTable() {
        const searchText = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        let visibleRows = 0;

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            if (row.classList.contains('no-results-row')) {
                continue;
            }

            const cells = row.getElementsByTagName('td');
            if (cells.length < 7) {
                continue;
            }

            const rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(' ');
            
            // This is the key part for the status filter
            const statusCellText = cells[6].textContent.trim().toLowerCase();
            const statusMatch = statusValue === '' || statusCellText.replace(/ /g, '_') === statusValue;

            const textMatch = rowText.includes(searchText);

            if (textMatch && statusMatch) {
                row.style.display = '';
                visibleRows++;
            } else {
                row.style.display = 'none';
            }
        }

        if (noResultsRow) {
            noResultsRow.style.display = visibleRows === 0 ? '' : 'none';
        }
    }

    if (searchInput && statusFilter) {
        searchInput.addEventListener('keyup', filterTable);
        statusFilter.addEventListener('change', filterTable);
    }
});
</script>

</body>
</html>