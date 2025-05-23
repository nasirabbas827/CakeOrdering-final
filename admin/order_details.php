<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    echo "Invalid Request.";
    exit;
}

$order_id = intval($_GET['order_id']);
$stmt = $conn->prepare("SELECT * FROM Orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "Order not found.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>
    <div class="container">
        <h3>Order #<?php echo $order['order_id']; ?> Details</h3>
        <hr>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['name']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
        <p><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
        <p><strong>Payment:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
        <p><strong>Amount:</strong> Rs. <?php echo $order['total_amount']; ?></p>
        <p><strong>Estimated Time:</strong> <?php echo $order['estimated_time']; ?></p>
        <p><strong>Created:</strong> <?php echo $order['created_at']; ?></p>

        <button onclick="window.print();" class="btn btn-secondary mt-3">Print</button>
        <a href="admin_orders.php" class="btn btn-primary mt-3">Back to Orders</a>
    </div>
</body>
</html>
