<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"]) || !isset($_GET['order_id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION["id"];

// Validate order ownership
$stmt = $conn->prepare("SELECT * FROM Orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "Order not found or unauthorized.";
    exit;
}

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['voucher'])) {
    $file = $_FILES['voucher'];
    $upload_dir = 'vouchers/';
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = time() . '_' . basename($file['name']);
    $target_file = $upload_dir . $filename;

    if (in_array($ext, $allowed_ext) && $file['size'] < 5 * 1024 * 1024) {
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // Insert voucher
            $stmt = $conn->prepare("INSERT INTO payment_vouchers (order_id, image_path, uploaded_at, verified) VALUES (?, ?, NOW(), 0)");
            $stmt->bind_param("is", $order_id, $target_file);
            $stmt->execute();
            $stmt->close();

            // Update order payment status
            $conn->query("UPDATE Orders SET payment_status = 'paid' WHERE order_id = $order_id");

            $success = "Voucher uploaded successfully. Your payment will be verified shortly.";
        } else {
            $error = "Failed to upload file.";
        }
    } else {
        $error = "Invalid file type or file too large.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Upload Payment Voucher</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>
<div class="container mt-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h4>Upload Payment Voucher for Order #<?= $order['order_id'] ?></h4>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Upload Image (JPG, PNG, GIF):</label>
            <input type="file" name="voucher" class="form-control-file" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload Voucher</button>
        <a href="my_orders.php" class="btn btn-secondary">Back to Orders</a>
    </form>
</div>
</div>
</div>
</body>
</html>
