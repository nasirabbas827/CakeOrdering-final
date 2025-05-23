<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$order_success = false;

// Handle order submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['place_order'])) {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);

    if (!empty($name) && !empty($address) && !empty($phone)) {
        // Fetch cart items
        $stmt = $conn->prepare("SELECT cakes.cake_id, cakes.price, cart.quantity 
                                FROM cart JOIN cakes ON cakes.cake_id = cart.cake_id 
                                WHERE cart.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (count($items) > 0) {
            $total_amount = 0;
            foreach ($items as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }

            // Insert into Orders
            $stmt = $conn->prepare("INSERT INTO Orders (user_id, total_amount, estimated_time, name, address, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $estimated_time = "45-60 minutes";
            $stmt->bind_param("idssss", $user_id, $total_amount, $estimated_time, $name, $address, $phone);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();

            // Insert order items
            $stmt = $conn->prepare("INSERT INTO Order_Items (order_id, cake_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmt->bind_param("iiid", $order_id, $item['cake_id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }
            $stmt->close();

            // Clear cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            $order_success = true;
        }
    }
}

// Fetch cart again for display
$stmt = $conn->prepare("SELECT cakes.name, cakes.price, cakes.image, cart.quantity 
                        FROM cart JOIN cakes ON cart.cake_id = cakes.cake_id 
                        WHERE cart.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

    <style> img { max-width: 100px; height: auto; } </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Checkout</h2>

    <?php if ($order_success): ?>
        <div class="alert alert-success">Your order has been placed successfully!</div>
    <?php endif; ?>

    <?php if (count($cart_items) > 0): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Image</th>
                <th>Cake</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $grand_total = 0; ?>
            <?php foreach ($cart_items as $item): 
                $item_total = $item['price'] * $item['quantity'];
                $grand_total += $item_total;
            ?>
            <tr>
                <td><img src="admin/<?= htmlspecialchars($item['image']) ?>"></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td>Rs. <?= number_format($item['price'], 2) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>Rs. <?= number_format($item_total, 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4" class="text-right"><strong>Grand Total:</strong></td>
                <td><strong>Rs. <?= number_format($grand_total, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <h4 class="mt-4">Delivery Information</h4>
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <form method="POST" action="checkout.php">
        <div class="form-group">
            <label for="name">Full Name *</label>
            <input type="text" name="name" required class="form-control">
        </div>
        <div class="form-group">
            <label for="address">Address *</label>
            <textarea name="address" required class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number *</label>
            <input type="text" name="phone" required class="form-control">
        </div>
        <button type="submit" name="place_order" class="btn btn-success">Place Order</button>
    </form>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>
</div>
</div>
</body>
</html>
