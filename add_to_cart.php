<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$cake_id = intval($_POST['cake_id']);

// Check if already in cart
$check = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND cake_id = ?");
$check->bind_param("ii", $user_id, $cake_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    // Already in cart
    echo "<script>alert('Cake already in cart!'); window.location.href='home.php';</script>";
} else {
    $stmt = $conn->prepare("INSERT INTO cart (user_id, cake_id, quantity) VALUES (?, ?, 1)");
    $stmt->bind_param("ii", $user_id, $cake_id);
    if ($stmt->execute()) {
        echo "<script>alert('Cake added to cart!'); window.location.href='home.php';</script>";
    } else {
        echo "<script>alert('Failed to add cake to cart.'); window.location.href='home.php';</script>";
    }
}
?>
