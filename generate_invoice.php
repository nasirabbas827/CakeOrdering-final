<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"]) || !isset($_GET['order_id'])) exit;

$user_id = $_SESSION["id"];
$order_id = intval($_GET['order_id']);

// Fetch order
$stmt = $conn->prepare("SELECT * FROM Orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "Invalid order.";
    exit;
}

// Fetch items
$stmt = $conn->prepare("SELECT cakes.name, order_items.quantity, order_items.price 
                        FROM order_items 
                        JOIN cakes ON cakes.cake_id = order_items.cake_id 
                        WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div id="invoiceArea">
    <h5>Order Invoice - #<?= $order['order_id'] ?></h5>
    <p><strong>Customer:</strong> <?= htmlspecialchars($order['name']) ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
    <p><strong>Date:</strong> <?= $order['created_at'] ?></p>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Cake</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>Rs. <?= number_format($item['price'], 2) ?></td>
                <td>Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="text-right"><strong>Grand Total:</strong> Rs. <?= number_format($order['total_amount'], 2) ?></p>
</div>

<div class="text-right mt-3">
    <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
</div>
