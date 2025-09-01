<?php
require_once 'config.php';

// start session & authorize early
session_start();
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'inventory_manager'])) {
    header("Location: index.php");
    exit();
}

// Function to get ENUM values from a table column (robust)
function get_enum_values($pdo, $table, $column) {
    $tableClean = str_replace('`','', $table);
    $colClean = str_replace('`','', $column);
    // safer query and defensive checks
    $stmt = $pdo->query("SHOW COLUMNS FROM `".$tableClean."` LIKE " . $pdo->quote($colClean));
    if (!$stmt) return [];
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || !isset($row['Type'])) return [];
    if (stripos($row['Type'], 'enum(') === false) return [];
    $enum_list = substr($row['Type'], 5, -1); // remove "enum(" and trailing ")"
    $enum_list = str_replace("'", "", $enum_list);
    if ($enum_list === '') return [];
    return explode(",", $enum_list);
}

// Enums for dropdown fields (after auth)
$location_enums = get_enum_values($pdo, 'inventory', 'location');
$supplier_enums = get_enum_values($pdo, 'inventory', 'supplier');
$productCategory_enums = get_enum_values($pdo, 'inventory', 'product_category');
$brand_enums = get_enum_values($pdo, 'inventory', 'brand');
$unit_enums = get_enum_values($pdo, 'inventory', 'unit');


// ---------- Status message (feedback after operations) ----------

$message = '';
$message_type = '';

// ---------- Handle POST actions (add / edit / delete) ----------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Handle DELETE via POST
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $deleteId = intval($_POST['id'] ?? 0);
        if ($deleteId > 0) {
            $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
            if ($stmt->execute([$deleteId])) {
                $message = "Item #$deleteId has been deleted.";
                $message_type = 'success';
            } else {
                $message = "Error deleting item #$deleteId.";
                $message_type = 'error';
            }
        } else {
            $message = "Invalid id for delete.";
            $message_type = 'error';
        }
    }

    // ADD ITEM
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $item_name = trim($_POST['item_name'] ?? '');
        $product_category = trim($_POST['product_category'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $unit = trim($_POST['unit'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 0);
        $kcs_purchasePrice = floatval($_POST['kcs_purchasePrice'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $reorder_threshold = intval($_POST['reorder_threshold'] ?? 0);
        $item_code = trim($_POST['item_code'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $supplier = trim($_POST['supplier'] ?? '');
        $lastPurchase_date = trim($_POST['lastPurchase_date'] ?? '');
        $expiry_date = trim($_POST['expiry_date'] ?? '');

        // Validate inputs
        if (!empty($item_name) && $quantity >= 0 && $price >= 0) {
            $stmt = $pdo->prepare("INSERT INTO inventory (item_name, product_category, brand, unit, quantity, kcs_purchasePrice, price, category, reorder_threshold, item_code, location, supplier, lastPurchase_date, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$item_name, $product_category, $brand, $unit, $quantity, $kcs_purchasePrice, $price, $category, $reorder_threshold, $item_code, $location, $supplier, $lastPurchase_date, $expiry_date])) {
                $message = "Item added successfully.";
                $message_type = 'success';
            } else {
                $message = "Error adding item.";
                $message_type = 'error';
            }
        } else {
            $message = "Invalid input. Please check all fields.";
            $message_type = 'error';
        }
    }

    // EDIT ITEM
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $item_name = trim($_POST['item_name'] ?? '');
        $product_category = trim($_POST['product_category'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $unit = trim($_POST['unit'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 0);
        $kcs_purchasePrice = floatval($_POST['kcs_purchasePrice'] ?? 0); // float
        $price = floatval($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $reorder_threshold = intval($_POST['reorder_threshold'] ?? 0);
        $item_code = trim($_POST['item_code'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $supplier = trim($_POST['supplier'] ?? '');
        $lastPurchase_date = trim($_POST['lastPurchase_date'] ?? '');
        $expiry_date = trim($_POST['expiry_date'] ?? '');

        //update
        if ($id > 0 && !empty($item_name) && $quantity >= 0 && $price >= 0) {
            // Fetch current values for audit
            $stmt_old = $pdo->prepare("SELECT * FROM inventory WHERE id = ?");
            $stmt_old->execute([$id]);
            $old_item = $stmt_old->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("UPDATE inventory SET item_name = ?, product_category =?, brand = ?, unit = ?, quantity = ?, kcs_purchasePrice = ?, price = ?, category = ?, reorder_threshold = ?, item_code = ?, location = ?, supplier = ?, lastPurchase_date = ?, expiry_date = ? WHERE id = ?");
            if ($stmt->execute([$item_name, $product_category, $brand, $unit, $quantity, $kcs_purchasePrice, $price, $category, $reorder_threshold, $item_code, $location, $supplier, $lastPurchase_date, $expiry_date, $id])) {
                // Compare old and new values to log changes
                $fields = [
                    'item_name' => $item_name,
                    'product_category' => $product_category,
                    'brand' => $brand,
                    'unit' => $unit,
                    'quantity' => $quantity,
                    'kcs_purchasePrice' => $kcs_purchasePrice,
                    'price' => $price,
                    'category' => $category,
                    'reorder_threshold' => $reorder_threshold,
                    'item_code' => $item_code,
                    'location' => $location,
                    'supplier' => $supplier,
                    'lastPurchase_date' => $lastPurchase_date,
                    'expiry_date' => $expiry_date
                ];
                $changed_fields = [];
                if ($old_item) {
                    foreach ($fields as $field => $new_value) {
                        if (isset($old_item[$field]) && $old_item[$field] != $new_value) {
                            $changed_fields[] = $field;
                        }
                    }
                }
                $changed_str = !empty($changed_fields) ? "Fields updated: " . implode(', ', $changed_fields) . "." : "No fields changed.";
                $message = "Item #$id updated successfully. $changed_str";
                $message_type = 'success';
            } else {
                $message = "Error updating item #$id.";
                $message_type = 'error';
            }
        } else {
            $message = "Invalid input for item #$id.";
            $message_type = 'error';
        }
    }
}

// Keep backward-compatible GET delete (optional)
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $deleteId = intval($_GET['id']);
    if ($deleteId > 0) {
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
        if ($stmt->execute([$deleteId])) {
            $message = "Item #$deleteId has been deleted.";
            $message_type = 'success';
        } else {
            $message = "Error deleting item #$deleteId.";
            $message_type = 'error';
        }
    }
}

// Search and filter logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT * FROM inventory";
$params = [];

if ($search) {
    $sql .= " WHERE item_name LIKE ?";
    $params[] = "%$search%";
}

if ($category_filter) {
    $sql .= ($search ? " AND" : " WHERE") . " category = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_items = count($items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory</title>
    <link rel="stylesheet" href="css/manage_inventory.css">
    <!-- DataTables CSS -->
<link rel="stylesheet" href="//cdn.datatables.net/2.3.3/css/dataTables.dataTables.min.css">

<!-- jQuery (required for DataTables if not using the ES6 version) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="//cdn.datatables.net/2.3.3/js/dataTables.min.js"></script>

    <style>
        .form-columns {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .form-column {
            flex: 0 0 48%;
            display: flex;
            flex-direction: column;
        }
        .form-column .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 1rem;
        }
        .form-column .form-group label {
            margin-bottom: 0.5rem;
        }
        .collapsible-container {
            display: none;
            overflow: hidden;
        }
        .collapsible-button {
            background-color: #28a745;
            color: white;
            cursor: pointer;
            padding: 18px;
            width: auto;
            border: none;
            text-align: center;
            outline: none;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            border-radius: 10px;
        }
        .collapsible-button:hover {
            background-color: #269d41;
        }
        
    .button-container {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

    select {
    font-size: 14px;        /* bigger text */
    padding: 6px 10px;      /* better spacing */
    min-width: 200px;       /* wider dropdown */
    }

    select option {
    font-size: 14px;        /* ensures dropdown text is readable */
    padding: 5px;
    }
    </style>
</head>
<body>

    <div class="header">
        <h1>Inventory Management</h1>
        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>

    <div class="container">

        <?php if ($message): ?>
            <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="button-container">
            <button type="button" id="add-item-btn" class="collapsible-button">Add New Item</button>
            <a href="inventory_reports.php" class="collapsible-button" style="text-decoration: none;">View Reports</a>
        </div>
        <div id="add-item-container" class="collapsible-container">
            <div class="form-container">
                <form class="add-form" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-columns">
                        <div class="form-column">
                            <div class="form-group">
                                <label for="item_code">Item Code</label>
                                <input type="text" id="item_code" name="item_code">
                            </div>
                            <div class="form-group">
                                <label for="item_name">Item Name</label>
                                <input type="text" id="item_name" name="item_name" required>
                            </div>
                            <div class="form-group">
                                <label for="product_category">Product Category</label>
                                <select id="product_category" name="product_category">
                                    <option value="">Select Product Category</option>
                                    <?php foreach ($productCategory_enums as $productCategory_option): ?>
                                        <option value="<?= htmlspecialchars($productCategory_option) ?>"><?= htmlspecialchars($productCategory_option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="brand">Brand</label>
                                <select id="brand" name="brand">
                                    <option value="">Select Brand</option>
                                    <?php foreach ($brand_enums as $brand_option): ?>
                                        <option value="<?= htmlspecialchars($brand_option) ?>"><?= htmlspecialchars($brand_option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="unit">Unit</label>
                                <select id="unit" name="unit">
                                    <option value="">Select Unit</option>
                                    <?php foreach ($unit_enums as $unit_option): ?>
                                        <option value="<?= htmlspecialchars($unit_option) ?>"><?= htmlspecialchars($unit_option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="kcs_purchasePrice">KCS Purchase Price (₱)</label>
                                <input type="number" id="kcs_purchasePrice" step="0.01" name="kcs_purchasePrice" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="price">Price (₱)</label>
                                <input type="number" id="price" step="0.01" name="price" min="0" required>
                            </div>
                        </div>

                        <div class="form-column">
                         <div class="form-group">
                                <label for="category">Category</label>
                                <select id="category" name="category">
                                    <option value="">Select Category</option>
                                    <option value="non-moving">Non-Moving</option>
                                    <option value="slow-moving">Slow-Moving</option>
                                    <option value="fast-moving">Fast-Moving</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="reorder_threshold">Reorder Threshold</label>
                                <input type="number" id="reorder_threshold" name="reorder_threshold" min="0">
                            </div>
                            <div class="form-group">
                                <label for="location">Location</label>
                                <select id="location" name="location">
                                    <option value="">Select Location</option>
                                    <?php foreach ($location_enums as $location_option): ?>
                                        <option value="<?= htmlspecialchars($location_option) ?>"><?= htmlspecialchars($location_option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="supplier">Supplier</label>
                                <select id="supplier" name="supplier">
                                    <option value="">Select Supplier</option>
                                    <?php foreach ($supplier_enums as $supplier_option): ?>
                                        <option value="<?= htmlspecialchars($supplier_option) ?>"><?= htmlspecialchars($supplier_option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="lastPurchase_date">Last Purchase Date</label>
                                <input type="date" id="lastPurchase_date" name="lastPurchase_date">
                            </div>
                            <div class="form-group">
                                <label for="expiry_date">Expiry Date</label>
                                <input type="date" id="expiry_date" name="expiry_date">
                            </div>
                            <button type="submit">Add Item</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="filters">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search by item name..." value="<?= htmlspecialchars($search) ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <option value="non-moving" <?= $category_filter === 'non-moving' ? 'selected' : '' ?>>Non-Moving</option>
                    <option value="slow-moving" <?= $category_filter === 'slow-moving' ? 'selected' : '' ?>>Slow-Moving</option>
                    <option value="fast-moving" <?= $category_filter === 'fast-moving' ? 'selected' : '' ?>>Fast-Moving</option>
                </select>
                <button type="submit">Filter</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Inventory List (<?= $total_items ?> items)</h2>
            <div class="toggle-columns">
    <strong>Toggle Columns:</strong><br>
    <label><input type="checkbox" class="toggle-col" data-column="3"> Product Category</label>
    <label><input type="checkbox" class="toggle-col" data-column="5"> Unit</label>
    <label><input type="checkbox" class="toggle-col" data-column="7"> KCS Purchase Price</label>
    <label><input type="checkbox" class="toggle-col" data-column="10"> Reorder Threshold</label>
    <label><input type="checkbox" class="toggle-col" data-column="11"> Location</label>
    <label><input type="checkbox" class="toggle-col" data-column="12"> Supplier</label>
    <label><input type="checkbox" class="toggle-col" data-column="13"> Last Purchase Date</label>
    <label><input type="checkbox" class="toggle-col" data-column="14"> Expiry Date</label>
            </div>
            <table id="inventoryTable" class="inventory-table display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Product Category</th>
                        <th>Brand</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>KCS Purchase Price (₱)</th>
                        <th>Price (₱)</th>
                        <th>Category</th>
                        <th>Reorder Threshold</th>
                        <th>Location</th>
                        <th>Supplier</th>
                        <th>Last Purchase Date</th>
                        <th>Expiry Date</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="16" style="text-align:center;">No items found in inventory.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                                <td><?= $item['id'] ?></td>
                                <td>
                                    <input type="text" name="item_code" value="<?= htmlspecialchars($item['item_code']) ?>">
                                </td>
                                <td class="item-name-cell">
                                    <input type="text" name="item_name" value="<?= htmlspecialchars($item['item_name']) ?>" required>
                                </td>
                                 <td>
                                    <select name="product_category">
                                        <option value="">Select Product Category</option>
                                        <?php foreach ($productCategory_enums as $productCategory_option): ?>
                                            <option value="<?= htmlspecialchars($productCategory_option) ?>" <?= ($item['product_category'] === $productCategory_option) ? 'selected' : '' ?>><?= htmlspecialchars($productCategory_option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="brand">
                                        <option value="">Select Brand</option>
                                        <?php foreach ($brand_enums as $brand_option): ?>
                                            <option value="<?= htmlspecialchars($brand_option) ?>" <?= ($item['brand'] === $brand_option) ? 'selected' : '' ?>><?= htmlspecialchars($brand_option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                 <td>
                                    <select name="unit">
                                        <option value="">Select Unit</option>
                                        <?php foreach ($unit_enums as $unit_option): ?>
                                            <option value="<?= htmlspecialchars($unit_option) ?>" <?= ($item['unit'] === $unit_option) ? 'selected' : '' ?>><?= htmlspecialchars($unit_option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="kcs_purchasePrice" value="<?= $item['kcs_purchasePrice'] ?>" min="0" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="price" value="<?= $item['price'] ?>" min="0" required>
                                </td>
                                <td>
                                    <select name="category">
                                        <option value="" <?= !$item['category'] ? 'selected' : '' ?>>Select Category</option>
                                        <option value="non-moving" <?= $item['category'] === 'non-moving' ? 'selected' : '' ?>>Non-Moving</option>
                                        <option value="slow-moving" <?= $item['category'] === 'slow-moving' ? 'selected' : '' ?>>Slow-Moving</option>
                                        <option value="fast-moving" <?= $item['category'] === 'fast-moving' ? 'selected' : '' ?>>Fast-Moving</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="reorder_threshold" value="<?= $item['reorder_threshold'] ?>" min="0">
                                </td>
                                <td>
                                    <select name="location">
                                        <option value="">Select Location</option>
                                        <?php foreach ($location_enums as $location_option): ?>
                                            <option value="<?= htmlspecialchars($location_option) ?>" <?= ($item['location'] === $location_option) ? 'selected' : '' ?>><?= htmlspecialchars($location_option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="supplier">
                                        <option value="">Select Supplier</option>
                                        <?php foreach ($supplier_enums as $supplier_option): ?>
                                            <option value="<?= htmlspecialchars($supplier_option) ?>" <?= ($item['supplier'] === $supplier_option) ? 'selected' : '' ?>><?= htmlspecialchars($supplier_option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="date" name="lastPurchase_date" value="<?= $item['lastPurchase_date'] ?>">
                                </td>
                                <td>
                                    <input type="date" name="expiry_date" value="<?= $item['expiry_date'] ?>">
                                </td>
                                <td class="actions-cell">
                                    <button type="button" class="update-btn" data-id="<?= $item['id'] ?>">Update</button>
                                    <button type="button" class="delete-btn" data-id="<?= $item['id'] ?>">Delete</button>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        document.getElementById("add-item-btn").addEventListener("click", function() {
            var content = document.getElementById("add-item-container");
            if (content.style.display === "block") {
                content.style.display = "none";
            } else {
                content.style.display = "block";
            }
        });
    </script>

    <script>
$(document).ready(function() {
    var table = $('#inventoryTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        scrollX: true,
        columnDefs: [
            { targets: [], visible: false } // hide less important columns by default
        ]
    });

    // initialize checkbox states to reflect current visibility
    $('.toggle-col').each(function() {
        var colIdx = parseInt($(this).attr('data-column'), 10);
        var col = table.column(colIdx);
        $(this).prop('checked', !!col.visible());
    });

    // Toggle column visibility when checkboxes clicked
    $('.toggle-col').on('change', function() {
        var column = table.column($(this).attr('data-column'));
        column.visible(!column.visible());
    });

    // Update / Delete handlers: build and submit a POST form (keeps HTML valid)
    $(document).on('click', '.update-btn', function() {
        var id = $(this).data('id');
        var row = $(this).closest('tr');
        var fields = ['item_code','item_name','product_category','brand','unit','quantity','kcs_purchasePrice','price','category','reorder_threshold','location','supplier','lastPurchase_date','expiry_date'];
        var form = $('<form>').attr('method','POST').css('display','none');
        form.append($('<input>').attr({type:'hidden', name:'action', value:'edit'}));
        form.append($('<input>').attr({type:'hidden', name:'id', value:id}));
        fields.forEach(function(name){
            var el = row.find('[name="'+name+'"]');
            var val = el.length ? el.val() : '';
            form.append($('<input>').attr({type:'hidden', name:name, value: val}));
        });
        $('body').append(form);
        form.submit();
    });

    $(document).on('click', '.delete-btn', function() {
        if (!confirm('Are you sure you want to delete this item?')) return;
        var id = $(this).data('id');
        var form = $('<form>').attr('method','POST').css('display','none');
        form.append($('<input>').attr({type:'hidden', name:'action', value:'delete'}));
        form.append($('<input>').attr({type:'hidden', name:'id', value:id}));
        $('body').append(form);
        form.submit();
    });
});
</script>


</body>
</html>