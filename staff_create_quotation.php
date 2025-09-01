<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_order_id = intval($_POST['job_order_id']);
    $base_price = floatval($_POST['price']);

    $extra_services = isset($_POST['extra_services']) ? json_decode($_POST['extra_services'], true) : [];
    $used_products = isset($_POST['used_products']) ? json_decode($_POST['used_products'], true) : [];

    if (!is_array($extra_services)) $extra_services = [];
    if (!is_array($used_products)) $used_products = [];

    $quote_details = strip_tags($_POST['generated_quote'] ?? '');
    $total_price = $base_price;

    foreach ($extra_services as $svc) {
        $total_price += floatval($svc['cost']);
    }

    foreach ($used_products as $prod) {
        $quantity = intval($prod['qty']);
        $unit_price = floatval($prod['price']);
        $total_price += $unit_price * $quantity;
    }

    $stmt = $pdo->prepare("INSERT INTO quotations (job_order_id, quote_details, amount, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$job_order_id, $quote_details, $total_price]);

    $quotation_id = $pdo->lastInsertId();

    if (!empty($used_products)) {
        $productStmt = $pdo->prepare("INSERT INTO quotation_products (quotation_id, product_id, quantity, price_per_unit) VALUES (?, ?, ?, ?)");
        foreach ($used_products as $prod) {
            $product_id = intval($prod['id']);
            $quantity = intval($prod['qty']);
            $price = floatval($prod['price']);
            $productStmt->execute([$quotation_id, $product_id, $quantity, $price]);
        }
    }

    $updateStmt = $pdo->prepare("UPDATE job_orders SET status = 'pending' WHERE id = ?");
    $updateStmt->execute([$job_order_id]);

    // Notify customer
    $stmt = $pdo->prepare("SELECT a.user_id, u.full_name FROM appointments a JOIN users u ON a.user_id = u.id WHERE a.id = (SELECT appointment_id FROM job_orders WHERE id = ?)");
    $stmt->execute([$job_order_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $user_id = $result['user_id'];
        $full_name = $result['full_name'];
        $notification_message = "Hi " . $full_name . ", a new quotation has been generated for Job Order #" . $job_order_id . ".";
        $insertNotification = $pdo->prepare("INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')");
        $insertNotification->execute([$user_id, $notification_message]);
    }

    echo "<p style='color:green; text-align:center; padding:10px; background-color:#e8f5e9; border:1px solid #4caf50;'>Quotation created successfully.</p>";
}

$jobOrders = $pdo->query("
    SELECT 
        jo.id, 
        jo.diagnosis, 
        u.full_name, 
        s.name AS service_name,
        s.cost AS service_cost
    FROM job_orders jo
    JOIN appointments a ON jo.appointment_id = a.id
    JOIN users u ON a.user_id = u.id
    JOIN services s ON a.service_id = s.id
    LEFT JOIN quotations q ON q.job_order_id = jo.id
    WHERE jo.status != 'completed' AND q.id IS NULL
    ORDER BY jo.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$services = $pdo->query("SELECT id, name, cost FROM services")->fetchAll(PDO::FETCH_ASSOC);
$products = $pdo->query("SELECT id, item_name, price, quantity FROM inventory WHERE quantity > 0")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quotation</title>
    <link rel="stylesheet" href="css/qm.css">
</head>
<body>

    <div class="header">
        <h1>Create New Quotation</h1>
        <a href="staff_quotation_manager.php" class="back-link">Back to Quotation Manager</a>
    </div>

    <div class="container">
        <form method="POST" onsubmit="return captureQuoteDetails()">
            <div class="form-grid">
                <div class="form-section">
                    <h3>1. Select Job Order</h3>
                    <label for="job_order_id">Job Order:</label>
                    <select name="job_order_id" id="job_order_id" required onchange="updatePrice()">
                        <option value="">-- Select a Job Order --</option>
                        <?php foreach ($jobOrders as $order): ?>
                            <option value="<?= $order['id'] ?>" data-price="<?= $order['service_cost'] ?>"
                                data-customer="<?= htmlspecialchars($order['full_name']) ?>"
                                data-service="<?= htmlspecialchars($order['service_name']) ?>"
                                data-diagnosis="<?= htmlspecialchars($order['diagnosis']) ?>">
                                #<?= $order['id'] ?> - <?= htmlspecialchars($order['full_name']) ?> (<?= htmlspecialchars($order['service_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="price">Base Service Price (₱):</label>
                    <input type="number" name="price" id="price" step="0.01" required readonly>
                </div>

                <div class="form-section">
                    <h3>2. Add Items</h3>
                    <label for="additional_service">Additional Services:</label>
                    <div class="item-adder">
                        <select id="additional_service">
                            <option value="">-- Select Service --</option>
                            <?php foreach ($services as $svc): ?>
                                <option value="<?= $svc['id'] ?>" data-name="<?= htmlspecialchars($svc['name']) ?>" data-cost="<?= $svc['cost'] ?>">
                                    <?= htmlspecialchars($svc['name']) ?> (₱<?= $svc['cost'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" onclick="addService()">Add</button>
                    </div>
                    <ul id="extra-services-list"></ul>
                    <input type="hidden" id="extra_services" name="extra_services">

                    <label for="product_select">Products:</label>
                    <div class="item-adder">
                        <select id="product_select">
                            <option value="">-- Select Product --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['item_name']) ?>" data-price="<?= $p['price'] ?>" data-stock="<?= $p['quantity'] ?>">
                                    <?= htmlspecialchars($p['item_name']) ?> (₱<?= $p['price'] ?> | Stock: <?= $p['quantity'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" id="product_qty" value="1" min="1" placeholder="Qty">
                        <button type="button" onclick="addProduct()">Add</button>
                    </div>
                    <ul id="product-list"></ul>
                    <input type="hidden" name="used_products" id="used_products">
                </div>

                <div class="action-buttons">
                    <input type="hidden" id="generated_quote" name="generated_quote">
                    <button type="button" onclick="previewQuotation()">Preview Quotation</button>
                    <button type="submit">Create Final Quotation</button>
                </div>
            </div>
        </form>
    </div>

    <div id="quotation-preview" style="display:none;">
        <div id="quote-content-wrapper">
            <button id="close-preview-btn" type="button" onclick="closePreview()">X</button>
            <div id="quote-content">
                <h3>Quotation Preview</h3>
                <p id="preview-customer"></p>
                <table border="1" cellpadding="10">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Cost (PHP)</th>
                        </tr>
                    </thead>
                    <tbody id="preview-services"></tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total Estimated Cost</strong></td>
                            <td id="preview-total"></td>
                        </tr>
                    </tfoot>
                </table>
                <p id="preview-notes"></p>
            </div>
        </div>
    </div>

    <script>
    let extraServices = [];
    let usedProducts = [];

    function updatePrice() {
        const select = document.getElementById("job_order_id");
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption.getAttribute("data-price");
        document.getElementById("price").value = price || "";
    }

    function addService() {
        const dropdown = document.getElementById("additional_service");
        const selected = dropdown.options[dropdown.selectedIndex];
        if (!selected.value) return;

        const id = selected.value;
        const name = selected.getAttribute("data-name");
        const cost = parseFloat(selected.getAttribute("data-cost"));

        if (extraServices.find(s => s.id === id)) {
            alert('This service has already been added.');
            return;
        }

        extraServices.push({ id, name, cost });
        renderLists();
        document.getElementById("extra_services").value = JSON.stringify(extraServices);
    }

    function addProduct() {
        const dropdown = document.getElementById("product_select");
        const qtyInput = document.getElementById("product_qty");
        const selected = dropdown.options[dropdown.selectedIndex];
        if (!selected.value) return;

        const id = selected.value;
        const name = selected.getAttribute("data-name");
        const price = parseFloat(selected.getAttribute("data-price"));
        const stock = parseInt(selected.getAttribute("data-stock"));
        const qty = parseInt(qtyInput.value);

        if (isNaN(qty) || qty < 1) {
            alert("Please enter a valid quantity.");
            return;
        }
        if (qty > stock) {
            alert(`Quantity exceeds stock on hand (${stock}).`);
            return;
        }

        const existingProduct = usedProducts.find(p => p.id === id);
        if (existingProduct) {
            if (existingProduct.qty + qty > stock) {
                alert(`Total quantity exceeds stock on hand (${stock}).`);
                return;
            }
            existingProduct.qty += qty;
        } else {
            usedProducts.push({ id, name, price, qty });
        }

        renderLists();
        document.getElementById("used_products").value = JSON.stringify(usedProducts);
        qtyInput.value = 1;
    }
    
    function renderLists() {
        const serviceList = document.getElementById("extra-services-list");
        serviceList.innerHTML = '';
        extraServices.forEach((svc, index) => {
            const li = document.createElement("li");
            li.innerHTML = `${svc.name} - ₱${svc.cost.toFixed(2)}`;
            serviceList.appendChild(li);
        });

        const productList = document.getElementById("product-list");
        productList.innerHTML = '';
        usedProducts.forEach((prod, index) => {
            const li = document.createElement("li");
            li.innerHTML = `${prod.name} x${prod.qty} - ₱${(prod.price * prod.qty).toFixed(2)}`;
            productList.appendChild(li);
        });
    }

    function previewQuotation() {
        const select = document.getElementById("job_order_id");
        if (!select.value) {
            alert('Please select a job order first.');
            return;
        }
        const selectedOption = select.options[select.selectedIndex];
        const customerName = selectedOption.getAttribute("data-customer");
        const serviceName = selectedOption.getAttribute("data-service");
        const basePrice = parseFloat(document.getElementById("price").value);

        document.getElementById("preview-customer").innerHTML = `Dear ${customerName},<br><br>Thank you for choosing our services. Here is the quotation for your vehicle:`;

        let rows = `<tr><td>${serviceName} (Base Service)</td><td>${basePrice.toFixed(2)}</td></tr>`;
        let total = basePrice;

        extraServices.forEach(svc => {
            rows += `<tr><td>${svc.name}</td><td>${svc.cost.toFixed(2)}</td></tr>`;
            total += svc.cost;
        });

        usedProducts.forEach(prod => {
            const subtotal = prod.qty * prod.price;
            rows += `<tr><td>${prod.name} x${prod.qty}</td><td>${subtotal.toFixed(2)}</td></tr>`;
            total += subtotal;
        });

        document.getElementById("preview-services").innerHTML = rows;
        document.getElementById("preview-total").innerText = `₱ ${total.toFixed(2)}`;
        document.getElementById("preview-notes").innerHTML = `<strong>Notes:</strong> This quotation is valid for 30 days. Prices are subject to change based on final inspection.`;

        document.getElementById("quotation-preview").style.display = "flex";
    }

    function captureQuoteDetails() {
        if (!document.getElementById("job_order_id").value) {
            alert('Please select a job order before creating the quotation.');
            return false;
        }
        document.getElementById("generated_quote").value = document.getElementById("quote-content").innerHTML;
        return true;
    }

    function closePreview() {
        document.getElementById("quotation-preview").style.display = "none";
    }
    </script>

</body>
</html>