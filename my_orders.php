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
    <title>My Order History | Boutique Bakery</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #d4a373; --dark: #1a1a1a; --cream: #faf9f6; }
        body { background-color: var(--cream); font-family: 'Inter', sans-serif; color: #444; }
        
        .header-title { font-family: 'Playfair Display', serif; color: var(--dark); margin: 40px 0; }
        
        .order-card { 
            background: white; border: none; border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 30px;
        }
        
        .order-header { 
            background-color: #fcfaf8; padding: 20px 30px; 
            border-bottom: 1px solid #f1f1f1; display: flex; 
            justify-content: space-between; align-items: center; flex-wrap: wrap;
        }

        .status-badge {
            padding: 6px 16px; border-radius: 50px; font-size: 0.75rem; 
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .status-pending { background: #fff4e6; color: #d97706; }
        .status-completed { background: #ecfdf5; color: #059669; }
        
        .delivery-eta {
            color: #e63946; font-weight: 600; font-size: 0.9rem;
            background: #fff0f0; padding: 5px 12px; border-radius: 8px;
        }

        .order-body { padding: 30px; }
        
        .item-row { font-size: 0.95rem; padding: 10px 0; border-bottom: 1px dashed #eee; }
        .item-row:last-child { border-bottom: none; }

        .btn-action {
            border-radius: 10px; font-weight: 600; padding: 8px 20px; transition: 0.3s;
        }
        
        .invoice-btn { background: white; border: 1px solid #ddd; color: #666; }
        .invoice-btn:hover { background: #f8f8f8; color: var(--dark); }

        .pay-btn { background: var(--dark); color: white; border: none; }
        .pay-btn:hover { background: var(--gold); color: white; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mb-5">
    <h2 class="header-title text-center">My Pastry Orders</h2>

    <?php if (count($orders) === 0): ?>
        <div class="text-center py-5">
            <div style="font-size: 4rem;">🥐</div>
            <h4>No orders yet</h4>
            <p class="text-muted">Once you order a cake, it will appear here.</p>
            <a href="home.php" class="btn btn-dark rounded-pill px-5 mt-3">Start Shopping</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <span class="text-muted small text-uppercase font-weight-bold">Order #<?= $order['order_id'] ?></span>
                        <div class="small text-muted"><?= date('M d, Y • h:i A', strtotime($order['created_at'])) ?></div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="delivery-eta mr-3">ETA: <?= $order['estimated_time'] ?></span>
                        <span class="status-badge <?= $order['status'] == 'completed' ? 'status-completed' : 'status-pending' ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>
                </div>
                
                <div class="order-body">
                    <div class="row">
                        <div class="col-md-7">
                            <h6 class="font-weight-bold mb-3">Items Ordered</h6>
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="item-row d-flex justify-content-between">
                                    <span><?= $item['quantity'] ?>x <?= htmlspecialchars($item['name']) ?></span>
                                    <span class="text-muted">Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0" style="font-family: 'Playfair Display', serif;">Total Amount</span>
                                <span class="h5 mb-0 text-dark font-weight-bold">Rs. <?= number_format($order['total_amount'], 2) ?></span>
                            </div>
                        </div>
                        
                        <div class="col-md-5 mt-4 mt-md-0 border-left">
                            <h6 class="font-weight-bold mb-2">Delivery Address</h6>
                            <p class="small text-muted mb-4">
                                <strong><?= htmlspecialchars($order['name']) ?></strong><br>
                                <?= nl2br(htmlspecialchars($order['address'])) ?><br>
                                <span class="text-dark">📞 <?= htmlspecialchars($order['phone']) ?></span>
                            </p>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-action invoice-btn mr-2" onclick="generateInvoice(<?= $order['order_id'] ?>)">
                                    Voucher
                                </button>
                                
                                <?php if ($order['payment_status'] === 'unpaid'): ?>
                                    <a href="pay_now.php?order_id=<?= $order['order_id'] ?>" class="btn btn-action pay-btn">
                                        Pay Now
                                    </a>
                                <?php else: ?>
                                    <span class="text-success small font-weight-bold ml-2">✓ Paid</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden; border: none;">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title font-weight-bold">Invoice Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body" id="invoice-content">
                </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-dark rounded-pill" onclick="window.print()">Print Invoice</button>
            </div>
        </div>
    </div>
</div>

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