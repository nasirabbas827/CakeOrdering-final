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

// Fetch Order Header
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Detail #<?= $order_id ?> | Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #d4a373;
            --accent-color: #faedcd;
            --text-dark: #2d2d2d;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: var(--text-dark);
        }

        h3, .invoice-brand { font-family: 'Playfair Display', serif; font-weight: 700; }

        .invoice-card {
            background: #fff;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .invoice-header {
            background: var(--accent-color);
            padding: 40px;
            border-bottom: 2px solid rgba(0,0,0,0.05);
        }

        .detail-section {
            padding: 30px 40px;
        }

        .label-style {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 700;
            color: #999;
            display: block;
            margin-bottom: 5px;
        }

        .info-data {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .status-pill {
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            background: #fff;
            border: 1px solid #ddd;
        }

        .amount-highlight {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .invoice-card { box-shadow: none; border: 1px solid #eee; }
            .container { max-width: 100% !important; }
        }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="row no-print mb-4">
        <div class="col-6">
            <a href="admin_orders.php" class="btn btn-link text-muted p-0">← Back to Orders</a>
        </div>
        <div class="col-6 text-right">
            <button onclick="window.print();" class="btn btn-outline-dark shadow-sm px-4">Print Invoice</button>
        </div>
    </div>

    <div class="invoice-card">
        <div class="invoice-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="invoice-brand text-uppercase" style="letter-spacing: 2px; color: #bc8a5f;">Boutique Bakery</span>
                    <h3 class="mt-2">Order #<?= $order['order_id'] ?></h3>
                    <p class="text-muted mb-0">Placed on: <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
                </div>
                <div class="col-md-6 text-md-right mt-3 mt-md-0">
                    <span class="label-style">Total Amount</span>
                    <div class="amount-highlight">Rs. <?= number_format($order['total_amount'], 2) ?></div>
                </div>
            </div>
        </div>

        <div class="row no-gutters">
            <div class="col-md-6 border-right">
                <div class="detail-section">
                    <h5 class="mb-4 font-weight-bold">Customer Information</h5>
                    
                    <div class="mb-4">
                        <span class="label-style">Full Name</span>
                        <div class="info-data"><?= htmlspecialchars($order['name']) ?></div>
                    </div>

                    <div class="mb-4">
                        <span class="label-style">Contact Number</span>
                        <div class="info-data"><?= htmlspecialchars($order['phone']) ?></div>
                    </div>

                    <div class="mb-0">
                        <span class="label-style">Delivery Address</span>
                        <div class="info-data" style="line-height: 1.6;">
                            <?= nl2br(htmlspecialchars($order['address'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="detail-section">
                    <h5 class="mb-4 font-weight-bold">Order Logistics</h5>

                    <div class="row mb-4">
                        <div class="col-6">
                            <span class="label-style">Order Status</span>
                            <span class="status-pill text-uppercase"><?= $order['status'] ?></span>
                        </div>
                        <div class="col-6">
                            <span class="label-style">Payment Status</span>
                            <span class="badge badge-<?= $order['payment_status'] == 'paid' ? 'success' : 'danger' ?> px-3 py-2">
                                <?= strtoupper($order['payment_status']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <span class="label-style">Estimated Completion</span>
                        <div class="info-data text-primary">
                            <?= !empty($order['estimated_time']) ? htmlspecialchars($order['estimated_time']) : 'Not set' ?>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded">
                        <small class="text-muted d-block mb-1 font-weight-bold uppercase">Admin Notes</small>
                        <p class="small mb-0 italic text-muted">Please ensure all cake decorations match the customer's custom request details if applicable.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-dark text-white p-3 text-center d-none d-print-block">
            <small>Thank you for choosing Boutique Bakery. This is a computer-generated invoice.</small>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>