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

    // Update verification status
    $stmt = $conn->prepare("UPDATE payment_vouchers SET verified = ? WHERE voucher_id = ?");
    $stmt->bind_param("ii", $verified, $voucher_id);
    $stmt->execute();
    $stmt->close();

    // Update order payment_status accordingly
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
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start = $_GET['start_date'];
    $end = $_GET['end_date'];
    $filter = "WHERE DATE(created_at) BETWEEN '$start' AND '$end'";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <h3>Manage Customer Orders</h3>

    <form class="form-inline mb-3" method="GET" action="admin_orders.php">
        <label for="start_date" class="mr-2">From:</label>
        <input type="date" name="start_date" class="form-control mr-3" required>
        <label for="end_date" class="mr-2">To:</label>
        <input type="date" name="end_date" class="form-control mr-3" required>
        <button type="submit" class="btn btn-primary">Filter</button>
        <button type="button" onclick="window.print();" class="btn btn-secondary ml-2">Print Orders</button>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Customer Info</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Estimated Time</th>
                    <th>Created</th>
                    <th>Update</th>
                    <th>Voucher</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $orders = $conn->query("SELECT * FROM Orders $filter ORDER BY created_at DESC");
                if ($orders->num_rows > 0) {
                    while ($row = $orders->fetch_assoc()) {
                        echo "<tr>
                            <td>#{$row['order_id']}</td>
                            <td><strong>Name:</strong> " . htmlspecialchars($row['name']) . "</td>
                            <td>Rs. {$row['total_amount']}</td>
                            <td>" . ucfirst($row['payment_status']) . "</td>
                            <td>
                                <form method='POST' class='form-inline'>
                                    <input type='hidden' name='order_id' value='{$row['order_id']}'>
                                    <select name='status' class='form-control form-control-sm mr-2'>
                                        <option " . ($row['status'] == 'processing' ? 'selected' : '') . ">processing</option>
                                        <option " . ($row['status'] == 'confirmed' ? 'selected' : '') . ">confirmed</option>
                                        <option " . ($row['status'] == 'dispatched' ? 'selected' : '') . ">dispatched</option>
                                        <option " . ($row['status'] == 'delivered' ? 'selected' : '') . ">delivered</option>
                                        <option " . ($row['status'] == 'rejected' ? 'selected' : '') . ">rejected</option>
                                    </select>
                            </td>
                            <td>
                                    <input type='text' name='estimated_time' class='form-control form-control-sm' value='" . htmlspecialchars($row['estimated_time']) . "'>
                            </td>
                            <td>{$row['created_at']}</td>
                            <td>
                                    <button type='submit' name='update_order' class='btn btn-sm btn-primary'>Update</button>
                                </form>
                                <form method='GET' action='order_details.php' class='mt-2'>
                                    <input type='hidden' name='order_id' value='{$row['order_id']}'>
                                    <button type='submit' class='btn btn-sm btn-outline-info'>View Details</button>
                                </form>
                            </td>
                            <td>";

                        $oid = $row['order_id'];
                        $vResult = $conn->query("SELECT * FROM payment_vouchers WHERE order_id = $oid");
                        if ($vResult && $vResult->num_rows > 0) {
                            $voucher = $vResult->fetch_assoc();
                            echo "
                            <button class='btn btn-sm btn-info' data-toggle='modal' data-target='#voucherModal$oid'>View</button>

                            <div class='modal fade' id='voucherModal$oid' tabindex='-1' role='dialog'>
                              <div class='modal-dialog modal-dialog-centered' role='document'>
                                <div class='modal-content'>
                                  <div class='modal-header'>
                                    <h5 class='modal-title'>Payment Voucher - Order #$oid</h5>
                                    <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                  </div>
                                  <div class='modal-body text-center'>
                                    <img src='../{$voucher['image_path']}' class='img-fluid mb-3' alt='Voucher Image'>
                                    <form method='POST'>
                                        <input type='hidden' name='voucher_id' value='{$voucher['voucher_id']}'>
                                        <input type='hidden' name='order_id' value='$oid'>
                                        <div class='form-group'>
                                            <select name='verified' class='form-control'>
                                                <option value='1' " . ($voucher['verified'] ? 'selected' : '') . ">Verified</option>
                                                <option value='0' " . (!$voucher['verified'] ? 'selected' : '') . ">Not Verified</option>
                                            </select>
                                        </div>
                                        <button type='submit' name='verify_payment' class='btn btn-success'>Update Verification</button>
                                    </form>
                                  </div>
                                </div>
                              </div>
                            </div>";
                        } else {
                            echo "<span class='text-muted'>No Voucher</span>";
                        }

                        echo "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No orders found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
