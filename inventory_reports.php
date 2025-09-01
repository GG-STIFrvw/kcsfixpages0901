<?php
require_once 'config.php';

session_start();
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'inventory_manager'])) {
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Reports</title>
    <link rel="stylesheet" href="css/manage_inventory.css">
</head>
<body>

    <div class="header">
        <h1>Inventory Reports</h1>
        <a href="inventory_management.php" class="back-link">Back to Inventory Management</a>
    </div>

    <div class="container">
        <div class="report-nav">
            <a href="?report=stock_levels" class="report-link">Current Stock Levels</a>
            <a href="?report=item_classification" class="report-link">Item Classification</a>
            <a href="?report=item_usage" class="report-link">Item Usage</a>
            <a href="?report=inventory_turnover" class="report-link">Inventory Turnover</a>
        </div>

        <div class="report-content">
            <?php
            $report_type = isset($_GET['report']) ? $_GET['report'] : '';

            if ($report_type === 'stock_levels') {
                // Fetch all inventory items
                $stmt = $pdo->query("SELECT * FROM inventory ORDER BY id ASC");
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <h2>Current Stock Levels</h2>
                <button onclick="window.print();" class="print-btn">Print Report</button>
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Price (₱)</th>
                            <th>Value (₱)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center;">No items found in inventory.</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $total_value = 0;
                            foreach ($items as $item): 
                                $value = $item['quantity'] * $item['price'];
                                $total_value += $value;
                            ?>
                                <tr>
                                    <td><?= $item['id'] ?></td>
                                    <td><?= htmlspecialchars($item['item_code']) ?></td>
                                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= number_format($item['price'], 2) ?></td>
                                    <td><?= number_format($value, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="5" style="text-align:right; font-weight:bold;">Total Inventory Value:</td>
                                <td style="font-weight:bold;"><?= number_format($total_value, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php
            } elseif ($report_type === 'item_classification') {
                // Fetch all inventory items
                $stmt = $pdo->query("SELECT * FROM inventory ORDER BY category, id ASC");
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Group items by category
                $grouped_items = [];
                foreach ($items as $item) {
                    $grouped_items[$item['category']][] = $item;
                }
                ?>
                <h2>Item Classification Report</h2>
                <button onclick="window.print();" class="print-btn">Print Report</button>
                <?php foreach ($grouped_items as $category => $category_items): ?>
                    <h3><?= htmlspecialchars(ucfirst($category)) ?></h3>
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Price (₱)</th>
                                <th>Value (₱)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $category_total_value = 0;
                            foreach ($category_items as $item): 
                                $value = $item['quantity'] * $item['price'];
                                $category_total_value += $value;
                            ?>
                                <tr>
                                    <td><?= $item['id'] ?></td>
                                    <td><?= htmlspecialchars($item['item_code']) ?></td>
                                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= number_format($item['price'], 2) ?></td>
                                    <td><?= number_format($value, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="5" style="text-align:right; font-weight:bold;">Total Value for <?= htmlspecialchars(ucfirst($category)) ?>:</td>
                                <td style="font-weight:bold;"><?= number_format($category_total_value, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php endforeach; ?>
            <?php
            } elseif ($report_type === 'item_usage') {
                // Fetch item usage data from inventory_log
                $stmt = $pdo->query("
                    SELECT 
                        i.item_name, 
                        i.item_code, 
                        SUM(il.quantity_used) as total_quantity_used
                    FROM inventory_log il
                    JOIN inventory i ON il.item_id = i.id
                    WHERE il.action = 'withdraw'
                    GROUP BY il.item_id
                    ORDER BY total_quantity_used DESC
                ");
                $usage_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <h2>Item Usage Report</h2>
                <button onclick="window.print();" class="print-btn">Print Report</button>
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Total Quantity Used</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usage_data)): ?>
                            <tr>
                                <td colspan="3" style="text-align:center;">No item usage data found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usage_data as $data): ?>
                                <tr>
                                    <td><?= htmlspecialchars($data['item_code']) ?></td>
                                    <td><?= htmlspecialchars($data['item_name']) ?></td>
                                    <td><?= $data['total_quantity_used'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php
            } elseif ($report_type === 'inventory_turnover') {
                // Calculate Cost of Goods Sold (COGS)
                $cogs_stmt = $pdo->query("
                    SELECT SUM(il.quantity_used * i.price) as total_cogs
                    FROM inventory_log il
                    JOIN inventory i ON il.item_id = i.id
                    WHERE il.action = 'withdraw'
                ");
                $cogs_data = $cogs_stmt->fetch(PDO::FETCH_ASSOC);
                $total_cogs = $cogs_data['total_cogs'] ?? 0;

                // Calculate Current Inventory Value
                $inv_value_stmt = $pdo->query("SELECT SUM(quantity * price) as total_inv_value FROM inventory");
                $inv_value_data = $inv_value_stmt->fetch(PDO::FETCH_ASSOC);
                $total_inv_value = $inv_value_data['total_inv_value'] ?? 0;

                // Calculate Inventory Turnover
                $inventory_turnover = ($total_inv_value > 0) ? $total_cogs / $total_inv_value : 0;
                ?>
                <h2>Inventory Turnover Report</h2>
                <button onclick="window.print();" class="print-btn">Print Report</button>
                <div class="report-summary">
                    <p><strong>Cost of Goods Sold (COGS):</strong> ₱ <?= number_format($total_cogs, 2) ?></p>
                    <p><strong>Current Inventory Value:</strong> ₱ <?= number_format($total_inv_value, 2) ?></p>
                    <p><strong>Inventory Turnover Rate:</strong> <?= number_format($inventory_turnover, 2) ?></p>
                    <br>
                    <p><strong>Note:</strong> Inventory turnover is calculated as COGS / Current Inventory Value. For a more accurate calculation, a historical average inventory value should be used.</p>
                </div>
            <?php
            } else {
                echo "<p>Please select a report to view.</p>";
            }
            ?>
        </div>
    </div>

</body>
</html>