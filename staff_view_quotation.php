
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

function fetchQuotationsByStatus($pdo, $status = null) {
    $sql = "
        SELECT 
            q.id, q.status, q.amount, q.quote_details, q.decline_note,
            jo.diagnosis, s.name AS service_name, s.cost AS service_cost,
            u.full_name AS customer_name
        FROM quotations q
        JOIN job_orders jo ON q.job_order_id = jo.id
        JOIN appointments a ON jo.appointment_id = a.id
        JOIN users u ON a.user_id = u.id
        JOIN services s ON a.service_id = s.id
    ";

    if ($status) {
        $sql .= " WHERE q.status = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query($sql);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$pending = fetchQuotationsByStatus($pdo, 'pending');
$accepted = fetchQuotationsByStatus($pdo, 'accepted');
$declined = fetchQuotationsByStatus($pdo, 'declined');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Quotations - Staff View</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/admin_viewQM.css">
</head>
<body>

    <div class="header">
        <h1>Quotation Overview</h1>
        <a href="staff_quotation_manager.php" class="back-link">Back to Quotation Manager</a>
    </div>

    <div class="container">
        <div class="tabs">
            <button class="tab active" onclick="showTab('pending')">Pending (<?= count($pending) ?>)</button>
            <button class="tab" onclick="showTab('accepted')">Accepted (<?= count($accepted) ?>)</button>
            <button class="tab" onclick="showTab('declined')">Declined (<?= count($declined) ?>)</button>
        </div>

        <div id="pending" class="tab-content active">
            <h3>Pending Quotations</h3>
            <?php if (!empty($pending)): ?>
                <table class="quotations-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Diagnosis</th>
                            <th>Amount</th>
                            <th>Details</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending as $q): ?>
                            <tr>
                                <td><?= $q['id'] ?></td>
                                <td><?= htmlspecialchars($q['customer_name']) ?></td>
                                <td><?= htmlspecialchars($q['service_name']) ?></td>
                                <td><?= htmlspecialchars($q['diagnosis']) ?></td>
                                <td>₱<?= number_format($q['amount'], 2) ?></td>
                                <td>
                                    <span class="details-toggle" onclick="toggleDetails('details-<?= $q['id'] ?>')">View Details</span>
                                    <div id="details-<?= $q['id'] ?>" class="quote-details" style="display: none;">
                                        <?= nl2br(htmlspecialchars($q['quote_details'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <button class="action-button" onclick="toggleEditForm('edit-<?= $q['id'] ?>')">Update Status</button>
                                    <form id="edit-<?= $q['id'] ?>" method="POST" action="update_status.php" class="edit-form" style="display: none;">
                                        <input type="hidden" name="quote_id" value="<?= $q['id'] ?>">
                                        <select name="status" required>
                                            <option value="">Select status</option>
                                            <option value="accepted">Accept</option>
                                            <option value="declined">Decline</option>
                                        </select>
                                        <textarea name="note" placeholder="Optional note for decline/acceptance..." rows="3"><?= htmlspecialchars($q['decline_note']) ?></textarea>
                                        <button type="submit">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No pending quotations.</p>
            <?php endif; ?>
        </div>

        <div id="accepted" class="tab-content">
            <h3>Accepted Quotations</h3>
            <?php if (!empty($accepted)): ?>
                <table class="quotations-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Diagnosis</th>
                            <th>Amount</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accepted as $q): ?>
                            <tr>
                                <td><?= $q['id'] ?></td>
                                <td><?= htmlspecialchars($q['customer_name']) ?></td>
                                <td><?= htmlspecialchars($q['service_name']) ?></td>
                                <td><?= htmlspecialchars($q['diagnosis']) ?></td>
                                <td>₱<?= number_format($q['amount'], 2) ?></td>
                                <td><?= htmlspecialchars($q['decline_note']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No accepted quotations.</p>
            <?php endif; ?>
        </div>

        <div id="declined" class="tab-content">
            <h3>Declined Quotations</h3>
            <?php if (!empty($declined)): ?>
                <table class="quotations-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Diagnosis</th>
                            <th>Amount</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($declined as $q): ?>
                            <tr>
                                <td><?= $q['id'] ?></td>
                                <td><?= htmlspecialchars($q['customer_name']) ?></td>
                                <td><?= htmlspecialchars($q['service_name']) ?></td>
                                <td><?= htmlspecialchars($q['diagnosis']) ?></td>
                                <td>₱<?= number_format($q['amount'], 2) ?></td>
                                <td><?= htmlspecialchars($q['decline_note']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No declined quotations.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
            document.getElementById(tabId + '-tab').classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }

        function toggleDetails(id) {
            const el = document.getElementById(id);
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
        }

        function toggleEditForm(id) {
            const el = document.getElementById(id);
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
        }

        // Initialize the first tab as active on page load
        document.addEventListener('DOMContentLoaded', () => {
            showTab('pending');
        });
    </script>

</body>
</html>
