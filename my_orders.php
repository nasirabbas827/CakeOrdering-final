<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch orders
$sql = "SELECT * FROM Orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();
$orders = [];

while ($order = $orders_result->fetch_assoc()) {
    $order_id = $order['order_id'];

    // Fetch order items
    $items_stmt = $conn->prepare("
        SELECT cakes.name, order_items.quantity, order_items.price 
        FROM order_items 
        JOIN cakes ON cakes.cake_id = order_items.cake_id 
        WHERE order_id = ?
    ");
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    $order['items'] = $items_result->fetch_all(MYSQLI_ASSOC);
    $orders[] = $order;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Orders</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .time{
            color:red;
            margin: 20px;
        }
    </style>

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>My Orders</h2>

    <?php if (count($orders) === 0): ?>
        <p>You have not placed any orders yet.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Order ID:</strong> <?= $order['order_id'] ?> |
                    <strong>Status:</strong> <?= ucfirst($order['status']) ?> |
                    <strong>Payment:</strong> <?= ucfirst($order['payment_status']) ?> |
                    <strong class="time">Estimated Delivery: <?= $order['estimated_time'] ?></strong>
                    <strong>Placed on:</strong> <?= $order['created_at'] ?>
                </div>
                <div class="card-body">
                    <p><strong>Delivery To:</strong> <?= htmlspecialchars($order['name']) ?>, <?= htmlspecialchars($order['address']) ?>, <?= htmlspecialchars($order['phone']) ?></p>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Cake</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>Rs. <?= number_format($item['price'], 2) ?></td>
                                    <td>Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p><strong>Grand Total:</strong> Rs. <?= number_format($order['total_amount'], 2) ?></p>

                    <?php if ($order['payment_status'] === 'unpaid'): ?>
                        <button class="btn btn-info btn-sm" onclick="generateInvoice(<?= $order['order_id'] ?>)">Generate Invoice</button>
                        <a href="pay_now.php?order_id=<?= $order['order_id'] ?>" class="btn btn-success btn-sm">Pay Now</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invoice Voucher</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body" id="invoice-content">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function generateInvoice(orderId) {
    fetch(`generate_invoice.php?order_id=${orderId}`)
        .then(res => res.text())
        .then(data => {
            document.getElementById('invoice-content').innerHTML = data;
            $('#invoiceModal').modal('show');
        });
}
</script>
</body>
</html>
