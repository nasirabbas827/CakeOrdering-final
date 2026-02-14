<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
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

// Fetch current user details for auto-filling the form
$user_stmt = $conn->prepare("SELECT full_name, phone FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_stmt->bind_result($default_name, $default_phone);
$user_stmt->fetch();
$user_stmt->close();

// Fetch cart for display
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
    <title>Secure Checkout | Boutique Bakery</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #d4a373; --dark: #1a1a1a; --cream: #faf9f6; }
        body { background-color: var(--cream); font-family: 'Inter', sans-serif; color: #444; }
        
        .checkout-title { font-family: 'Playfair Display', serif; color: var(--dark); margin: 40px 0; }
        
        .checkout-card { 
            background: white; border: none; border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 30px;
        }
        
        .order-summary-item { border-bottom: 1px solid #eee; padding: 15px 0; }
        .order-summary-item:last-child { border-bottom: none; }
        
        .item-img { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; }
        
        .form-control {
            border-radius: 10px; padding: 12px; border: 1px solid #eee; background: #fdfdfd;
        }
        .form-control:focus { border-color: var(--gold); box-shadow: none; }
        
        label { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: #888; }
        
        .btn-order { 
            background: var(--dark); color: white; border: none; padding: 15px; 
            border-radius: 12px; font-weight: 700; width: 100%; transition: 0.3s;
        }
        .btn-order:hover { background: var(--gold); transform: translateY(-2px); }

        .success-box {
            text-align: center; padding: 60px; background: white; border-radius: 30px;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mb-5">
    <?php if ($order_success): ?>
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="success-box shadow-sm">
                    <div style="font-size: 4rem;">🎉</div>
                    <h2 class="checkout-title">Order Confirmed!</h2>
                    <p class="text-muted mb-4">Thank you for choosing Boutique Bakery. Your delicious treats are being prepared and will be with you shortly.</p>
                    <a href="my_orders.php" class="btn btn-dark rounded-pill px-5">Track My Order</a>
                </div>
            </div>
        </div>
    <?php elseif (count($cart_items) > 0): ?>
        <h2 class="checkout-title text-center">Complete Your Order</h2>
        
        <div class="row">
            <div class="col-lg-7">
                <div class="checkout-card">
                    <h5 class="mb-4" style="font-family: 'Playfair Display', serif;">Delivery Details</h5>
                    <form method="POST" action="checkout.php">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($default_name) ?>" required class="form-control" placeholder="Who are we delivering to?">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($default_phone) ?>" required class="form-control" placeholder="In case we need to call you">
                        </div>
                        <div class="form-group">
                            <label>Shipping Address</label>
                            <textarea name="address" required class="form-control" rows="4" placeholder="Street name, house number, area..."></textarea>
                        </div>
                        <div class="mt-4 p-3 rounded-lg" style="background: #fff9f4; border: 1px dashed var(--gold);">
                            <small class="text-muted"><strong>Note:</strong> Currently we only support Cash on Delivery (COD). Please have the exact amount ready for our rider.</small>
                        </div>
                        <button type="submit" name="place_order" class="btn btn-order mt-4 shadow">Place My Order</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="checkout-card bg-light border-0">
                    <h5 class="mb-4" style="font-family: 'Playfair Display', serif;">Your Selection</h5>
                    <?php 
                    $grand_total = 0;
                    foreach ($cart_items as $item): 
                        $item_total = $item['price'] * $item['quantity'];
                        $grand_total += $item_total;
                    ?>
                    <div class="order-summary-item d-flex align-items-center">
                        <img src="admin/<?= htmlspecialchars($item['image']) ?>" class="item-img mr-3">
                        <div class="flex-grow-1">
                            <div class="font-weight-bold" style="font-size: 0.9rem;"><?= htmlspecialchars($item['name']) ?></div>
                            <small class="text-muted"><?= $item['quantity'] ?> x Rs. <?= number_format($item['price'], 2) ?></small>
                        </div>
                        <div class="font-weight-bold small">
                            Rs. <?= number_format($item_total, 2) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="mt-4 pt-3" style="border-top: 2px solid #fff;">
                        <div class="d-flex justify-content-between h5">
                            <span style="font-family: 'Playfair Display', serif;">Grand Total</span>
                            <span class="text-dark">Rs. <?= number_format($grand_total, 2) ?></span>
                        </div>
                        <small class="text-success font-weight-bold mt-2 d-block">Free Delivery Included</small>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="cart.php" class="text-muted small">Edit Basket</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <h4>Your cart is empty.</h4>
            <a href="home.php" class="btn btn-gold rounded-pill mt-3">Return to Shop</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>