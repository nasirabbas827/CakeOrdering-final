<?php
session_start();
include('config.php');

// Check admin login
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle order update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    $estimated_time = trim($_POST['estimated_time']);

    $stmt = $conn->prepare("UPDATE Orders SET status = ?, estimated_time = ? WHERE order_id = ?");
    $stmt->bind_param("ssi", $status, $estimated_time, $order_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_orders.php");
    exit;
}

// Handle payment verification toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_payment'])) {
    $voucher_id = intval($_POST['voucher_id']);
    $verified = intval($_POST['verified']);
    $order_id = intval($_POST['order_id']);

    $stmt = $conn->prepare("UPDATE payment_vouchers SET verified = ? WHERE voucher_id = ?");
    $stmt->bind_param("ii", $verified, $voucher_id);
    $stmt->execute();
    $stmt->close();

    $payment_status = $verified ? 'paid' : 'unpaid';
    $stmt = $conn->prepare("UPDATE Orders SET payment_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $payment_status, $order_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_orders.php");
    exit;
}

// Date filter
$filter = "";
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $start = $conn->real_escape_string($_GET['start_date']);
    $end = $conn->real_escape_string($_GET['end_date']);
    $filter = "WHERE DATE(created_at) BETWEEN '$start' AND '$end'";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders | Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #d4a373;
            --accent-color: #faedcd;
            --text-dark: #2d2d2d;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: var(--text-dark);
        }

        h3 { font-family: 'Playfair Display', serif; font-weight: 700; }

        .service-card {
            background: #fff;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            padding: 20px;
        }

        /* Order Status Badges */
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-processing { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #cce5ff; color: #004085; }
        .status-dispatched { background: #e2e3e5; color: #383d41; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }

        .table thead th {
            background: var(--accent-color);
            border: none;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .btn-update {
            background: var(--primary-color);
            border: none;
            color: white;
            font-weight: 600;
        }

        .btn-update:hover { background: #bc8a5f; color: white; }

        @media print {
            .navbar, .btn, .filter-section { display: none !important; }
            .service-card { box-shadow: none; border: 1px solid #eee; }
        }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container-fluid mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3>Manage Customer Orders</h3>
        </div>
        <div class="col-md-6 text-md-right filter-section">
            <form class="form-inline d-inline-block" method="GET">
                <input type="date" name="start_date" class="form-control form-control-sm mr-2" required>
                <input type="date" name="end_date" class="form-control form-control-sm mr-2" required>
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                <a href="admin_orders.php" class="btn btn-sm btn-outline-secondary">Reset</a>
            </form>
            <button onclick="window.print();" class="btn btn-sm btn-dark ml-2">Print Log</button>
        </div>
    </div>

    <div class="service-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Current Status</th>
                        <th>ETC</th>
                        <th>Verification</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $orders = $conn->query("SELECT * FROM Orders $filter ORDER BY created_at DESC");
                    if ($orders->num_rows > 0) {
                        while ($row = $orders->fetch_assoc()) {
                            $oid = $row['order_id'];
                            $status_class = "status-" . $row['status'];
                            ?>
                            <tr>
                                <td>
                                    <span class="font-weight-bold text-primary">#<?= $oid ?></span><br>
                                    <small class="text-muted"><?= date('M d, H:i', strtotime($row['created_at'])) ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['name']) ?></strong>
                                </td>
                                <td class="font-weight-bold">Rs. <?= number_format($row['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge badge-pill <?= $row['payment_status'] == 'paid' ? 'badge-success' : 'badge-danger' ?>">
                                        <?= ucfirst($row['payment_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method='POST' id='form<?= $oid ?>'>
                                        <input type='hidden' name='order_id' value='<?= $oid ?>'>
                                        <select name='status' class="form-control form-control-sm border-0 <?= $status_class ?>" onchange="this.className='form-control form-control-sm border-0 status-'+this.value">
                                            <?php 
                                            $opts = ['processing', 'confirmed', 'dispatched', 'delivered', 'rejected'];
                                            foreach($opts as $opt) {
                                                $sel = ($row['status'] == $opt) ? 'selected' : '';
                                                echo "<option value='$opt' $sel>$opt</option>";
                                            }
                                            ?>
                                        </select>
                                </td>
                                <td>
                                    <input type='text' name='estimated_time' class='form-control form-control-sm' value='<?= htmlspecialchars($row['estimated_time']) ?>' placeholder="e.g. 2 Hours">
                                </td>
                                <td>
                                    <?php
                                    $vResult = $conn->query("SELECT * FROM payment_vouchers WHERE order_id = $oid");
                                    if ($vResult && $vResult->num_rows > 0) {
                                        $voucher = $vResult->fetch_assoc();
                                        $vColor = $voucher['verified'] ? 'btn-success' : 'btn-info';
                                        echo "<button type='button' class='btn btn-xs $vColor' style='font-size:11px' data-toggle='modal' data-target='#voucherModal$oid'>
                                                " . ($voucher['verified'] ? 'Verified' : 'View Voucher') . "
                                              </button>";
                                    } else {
                                        echo "<span class='text-muted small italic'>No Voucher</span>";
                                    }
                                    ?>
                                </td>
                                <td class="text-right">
                                    <button type='submit' name='update_order' class='btn btn-sm btn-update mb-1'>Update</button>
                                    </form>
                                    <form method='GET' action='order_details.php'>
                                        <input type='hidden' name='order_id' value='<?= $oid ?>'>
                                        <button type='submit' class='btn btn-xs btn-link text-info p-0' style="font-size: 12px;">Order Items →</button>
                                    </form>
                                </td>
                            </tr>

                            <?php if ($vResult && $vResult->num_rows > 0): ?>
                            <div class='modal fade' id='voucherModal<?= $oid ?>' tabindex='-1' role='dialog'>
                                <div class='modal-dialog modal-dialog-centered'>
                                    <div class='modal-content border-0 shadow-lg'>
                                        <div class='modal-header bg-light'>
                                            <h5 class='modal-title'>Payment Check - #<?= $oid ?></h5>
                                            <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                        </div>
                                        <div class='modal-body text-center'>
                                            <div class="mb-3 rounded overflow-hidden shadow-sm">
                                                <img src='../<?= $voucher['image_path'] ?>' class='img-fluid' alt='Voucher'>
                                            </div>
                                            <form method='POST' class="bg-light p-3 rounded">
                                                <input type='hidden' name='voucher_id' value='<?= $voucher['voucher_id'] ?>'>
                                                <input type='hidden' name='order_id' value='<?= $oid ?>'>
                                                <label class="small font-weight-bold text-muted d-block text-left mb-2">VERIFY PAYMENT</label>
                                                <select name='verified' class='form-control mb-3'>
                                                    <option value='1' <?= $voucher['verified'] ? 'selected' : '' ?>>Verify & Mark Paid</option>
                                                    <option value='0' <?= !$voucher['verified'] ? 'selected' : '' ?>>Unverified / Pending</option>
                                                </select>
                                                <button type='submit' name='verify_payment' class='btn btn-primary btn-block'>Update Verification</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center py-5'>No orders found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>