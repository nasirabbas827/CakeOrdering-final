<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$message = "";

// Handle updates or removals
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update quantities
    if (isset($_POST['update_cart']) && isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $cart_id => $qty) {
            $qty = max(1, intval($qty));
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $qty, $cart_id, $user_id);
            $stmt->execute();
        }
        $message = "Your basket has been updated!";
    }

    // Remove item
    if (isset($_POST['remove_cart']) && isset($_POST['remove_id'])) {
        $remove_id = intval($_POST['remove_id']);
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $remove_id, $user_id);
        $stmt->execute();
        $message = "Item removed from your basket.";
    }
}

// Fetch cart items
$sql = "SELECT cart.id AS cart_id, cakes.name, cakes.price, cakes.image, cart.quantity, cakes.cake_id
        FROM cart
        JOIN cakes ON cart.cake_id = cakes.cake_id
        WHERE cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Your Basket | Boutique Bakery</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #d4a373; --dark: #1a1a1a; --cream: #faf9f6; }
        body { background-color: var(--cream); font-family: 'Inter', sans-serif; color: #444; }
        
        .page-header { font-family: 'Playfair Display', serif; color: var(--dark); margin: 40px 0; }
        
        .cart-card { 
            background: white; border: none; border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; 
        }
        
        .table thead { background-color: #fcfaf8; }
        .table thead th { 
            border: none; text-transform: uppercase; font-size: 0.75rem; 
            letter-spacing: 1px; color: #888; padding: 20px; 
        }
        
        .cart-img { width: 80px; height: 80px; object-fit: cover; border-radius: 12px; }
        
        .cake-name { font-weight: 600; color: var(--dark); }
        
        .quantity-control { 
            max-width: 80px; border-radius: 8px; border: 1px solid #eee; 
            text-align: center; padding: 5px; 
        }
        
        .summary-box { 
            background: var(--dark); color: white; border-radius: 20px; padding: 30px; 
        }
        
        .btn-gold { background: var(--gold); color: white; border: none; font-weight: 700; transition: 0.3s; }
        .btn-gold:hover { background: #bc8a5f; color: white; transform: translateY(-2px); }
        
        .remove-link { color: #ff6b6b; font-size: 0.8rem; cursor: pointer; background: none; border: none; padding: 0; }
        .remove-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mb-5">
    <h2 class="page-header text-center">Shopping Basket</h2>

    <?php if ($message): ?>
        <div class="alert alert-success border-0 shadow-sm text-center rounded-pill mb-4"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="text-center py-5">
            <div class="mb-4" style="font-size: 4rem;">🧁</div>
            <h4>Your basket is empty</h4>
            <p class="text-muted">It seems you haven't picked out any treats yet.</p>
            <a href="home.php" class="btn btn-gold rounded-pill px-5 mt-3">Browse Cakes</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="cart-card mb-4">
                    <form action="cart.php" method="POST" id="cartForm">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $grand_total = 0;
                                foreach ($cart_items as $item): 
                                    $item_total = $item['price'] * $item['quantity'];
                                    $grand_total += $item_total;
                                ?>
                                <tr>
                                    <td class="py-4">
                                        <div class="d-flex align-items-center">
                                            <img src="admin/<?= htmlspecialchars($item['image']) ?>" class="cart-img mr-3">
                                            <div>
                                                <div class="cake-name"><?= htmlspecialchars($item['name']) ?></div>
                                                <form method="POST" action="cart.php" class="d-inline">
                                                    <input type="hidden" name="remove_id" value="<?= $item['cart_id'] ?>">
                                                    <button type="submit" name="remove_cart" class="remove-link">Remove Item</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4">Rs. <?= number_format($item['price'], 2) ?></td>
                                    <td class="py-4">
                                        <input type="number" name="quantities[<?= $item['cart_id'] ?>]" 
                                               class="form-control quantity-control quantity-input" 
                                               value="<?= $item['quantity'] ?>" min="1" 
                                               data-price="<?= $item['price'] ?>">
                                    </td>
                                    <td class="py-4 text-right item-total font-weight-bold">
                                        Rs. <?= number_format($item_total, 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="p-3 bg-light text-right">
                            <button type="submit" name="update_cart" class="btn btn-outline-dark btn-sm rounded-pill px-4">Update Quantities</button>
                        </div>
                    </form>
                </div>
                <a href="home.php" class="btn btn-link text-muted p-0 small">← Continue Shopping</a>
            </div>

            <div class="col-lg-4">
                <div class="summary-box shadow">
                    <h5 class="mb-4" style="font-family: 'Playfair Display', serif;">Order Summary</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span id="subtotal">Rs. <?= number_format($grand_total, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span>Delivery</span>
                        <span class="text-success small">Calculated at checkout</span>
                    </div>
                    <hr style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="h6 mb-0">Total</span>
                        <span class="h4 mb-0" id="grand-total" style="color: var(--gold);">Rs. <?= number_format($grand_total, 2) ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-gold btn-block btn-lg rounded-pill shadow">Checkout Now</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', () => {
        let row = input.closest('tr');
        let price = parseFloat(input.getAttribute('data-price'));
        let qty = parseInt(input.value) || 1;
        let itemTotal = price * qty;
        row.querySelector('.item-total').textContent = `Rs. ${itemTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`;

        let grandTotal = 0;
        document.querySelectorAll('.quantity-input').forEach(q => {
            let p = parseFloat(q.getAttribute('data-price'));
            let v = parseInt(q.value) || 1;
            grandTotal += p * v;
        });
        const formattedTotal = `Rs. ${grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
        document.getElementById('grand-total').textContent = formattedTotal;
        document.getElementById('subtotal').textContent = formattedTotal;
    });
});
</script>
</body>
</html>