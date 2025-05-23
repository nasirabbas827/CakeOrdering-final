<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

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
        $message = "Cart updated successfully!";
    }

    // Remove item
    if (isset($_POST['remove_cart']) && isset($_POST['remove_id'])) {
        $remove_id = intval($_POST['remove_id']);
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $remove_id, $user_id);
        $stmt->execute();
        $message = "Item removed from cart!";
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
    <title>Your Cart</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        img { max-width: 100px; height: auto; }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Your Cart</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form action="cart.php" method="POST">
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Cake</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Remove</th>
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
                    <td><img src="admin/<?= htmlspecialchars($item['image']) ?>"></td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td>Rs. <?= number_format($item['price'], 2) ?></td>
                    <td>
                        <input type="number" name="quantities[<?= $item['cart_id'] ?>]" class="form-control quantity-input" value="<?= $item['quantity'] ?>" min="1" data-price="<?= $item['price'] ?>">
                    </td>
                    <td class="item-total">Rs. <?= number_format($item_total, 2) ?></td>
                    <td>
                        <form method="POST" action="cart.php" onsubmit="return confirm('Remove this item?')">
                            <input type="hidden" name="remove_id" value="<?= $item['cart_id'] ?>">
                            <button type="submit" name="remove_cart" class="btn btn-danger btn-sm">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4" class="text-right"><strong>Grand Total:</strong></td>
                    <td colspan="2" id="grand-total"><strong>Rs. <?= number_format($grand_total, 2) ?></strong></td>
                </tr>
            </tbody>
        </table>

        <div class="text-right">
            <button type="submit" name="update_cart" class="btn btn-primary">Update Cart</button>
            <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('input', () => {
        let row = input.closest('tr');
        let price = parseFloat(input.getAttribute('data-price'));
        let qty = parseInt(input.value) || 1;
        let itemTotal = price * qty;
        row.querySelector('.item-total').textContent = `Rs. ${itemTotal.toFixed(2)}`;

        let grandTotal = 0;
        document.querySelectorAll('.quantity-input').forEach(q => {
            let p = parseFloat(q.getAttribute('data-price'));
            let v = parseInt(q.value) || 1;
            grandTotal += p * v;
        });
        document.getElementById('grand-total').innerHTML = `<strong>Rs. ${grandTotal.toFixed(2)}</strong>`;
    });
});
</script>
</body>
</html>
