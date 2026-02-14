<?php
session_start();
include('config.php');

// Admin check
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Helper to fetch counts
function fetchCount($conn, $table, $condition = "1") {
    $sql = "SELECT COUNT(*) FROM $table WHERE $condition";
    $result = $conn->query($sql);
    return $result ? $result->fetch_row()[0] : 0;
}

// Summary Data based on your DB tables
$totalAdmins   = fetchCount($conn, "admins"); // Note: Ensure 'admins' table exists or use users where usertype='admin'
$totalCakes    = fetchCount($conn, "cakes", "is_available=1");
$totalOrders   = fetchCount($conn, "orders");
$totalUsers    = fetchCount($conn, "users");
$totalExpenses = fetchCount($conn, "expenses");

// Fetch Recent Orders (Last 5)
$recentOrders = $conn->query("SELECT order_id, user_id, total_amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5");

// Fetch Recent Expenses (Last 5)
$recentExpenses = $conn->query("SELECT expense_id, expense_type, amount, expense_date FROM expenses ORDER BY expense_date DESC LIMIT 5");

// Fetch Recent Custom Requests (Last 5) - New addition based on your DB
$recentCustomRequests = $conn->query("SELECT request_id, user_id, design_details, status, created_at FROM custom_requests ORDER BY created_at DESC LIMIT 5");

// Chart Data (Last 30 Days)
$orderStatusData = [];
$sql = "SELECT status, COUNT(*) as count FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY status";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $orderStatusData[$row['status']] = (int)$row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bakery Admin | Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bakery-gold: #d4a373;
            --bakery-cream: #faedcd;
            --text-main: #2d2d2d;
        }
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; color: var(--text-main); }
        h2, h4, h5 { font-family: 'Playfair Display', serif; font-weight: 700; }
        
        /* Dashboard Stats */
        .summary-card { border: none; border-radius: 15px; transition: 0.3s; }
        .summary-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .stat-val { font-size: 2rem; font-weight: 700; display: block; }
        .stat-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; }

        /* Tables & Sections */
        .recent-section { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); height: 100%; }
        .table thead th { background-color: #fdfaf0; border: none; font-size: 0.75rem; text-transform: uppercase; }
        
        /* Status Badges */
        .status-badge { padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
        .status-processing { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #cfe2ff; color: #084298; }
        .status-delivered { background: #d1e7dd; color: #0f5132; }
        .status-rejected { background: #f8d7da; color: #842029; }
        .status-pending { background: #e2e3e5; color: #41464b; }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container-fluid mt-4 px-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Bakery Overview</h2>
            <p class="text-muted">Welcome back! Here is what's happening today.</p>
        </div>
        <a href="reports.php" class="btn btn-dark px-4 py-2" style="border-radius: 10px;">View Full Report</a>
    </div>

    <div class="row mb-5">
        <div class="col-md-2 mb-3">
            <div class="card summary-card bg-primary text-white p-3">
                <span class="stat-label">Admins</span>
                <span class="stat-val"><?= $totalAdmins ?></span>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card summary-card bg-success text-white p-3">
                <span class="stat-label">Active Cakes</span>
                <span class="stat-val"><?= $totalCakes ?></span>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card summary-card bg-warning text-dark p-3">
                <span class="stat-label">Total Orders</span>
                <span class="stat-val"><?= $totalOrders ?></span>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card summary-card bg-info text-white p-3">
                <span class="stat-label">Users</span>
                <span class="stat-val"><?= $totalUsers ?></span>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card summary-card bg-danger text-white p-3">
                <span class="stat-label">Expenses Logged</span>
                <span class="stat-val"><?= $totalExpenses ?></span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="recent-section">
                <h5 class="mb-4">Recent Orders</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Status</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recentOrders->fetch_assoc()): ?>
                            <tr>
                                <td><a href="order_details.php?order_id=<?= $row['order_id'] ?>">#<?= $row['order_id'] ?></a></td>
                                <td><span class="status-badge status-<?= strtolower($row['status']) ?>"><?= $row['status'] ?></span></td>
                                <td class="text-right font-weight-bold">Rs. <?= number_format($row['total_amount'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="recent-section">
                <h5 class="mb-4">Custom Design Requests</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Details</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($req = $recentCustomRequests->fetch_assoc()): ?>
                            <tr>
                                <td class="small">User #<?= $req['user_id'] ?></td>
                                <td class="small text-truncate" style="max-width: 150px;"><?= htmlspecialchars($req['design_details']) ?></td>
                                <td><span class="status-badge status-<?= strtolower($req['status']) ?>"><?= $req['status'] ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-8 mb-4">
            <div class="recent-section">
                <h5>Order Status Analytics (30 Days)</h5>
                <hr>
                <div style="height: 300px;">
                    <canvas id="ordersStatusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="recent-section">
                <h5>Quick Actions</h5>
                <div class="list-group list-group-flush mt-3">
                    <a href="admin_expenses.php" class="list-group-item list-group-item-action border-0 mb-2 rounded bg-light">Log Business Expense</a>
                    <a href="add_cakes.php" class="list-group-item list-group-item-action border-0 mb-2 rounded bg-light">Add New Cake Flavor</a>
                    <a href="admin_reports.php" class="list-group-item list-group-item-action border-0 mb-2 rounded bg-light">Financial Summary</a>
                </div>
                <div class="alert alert-info mt-4 small border-0">
                    <strong>Note:</strong> All cake images must be uploaded to the <code>uploads/</code> directory. Use <code>cake.jpg</code> as the standard file format.
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('ordersStatusChart').getContext('2d');
    const orderData = <?= json_encode($orderStatusData) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(orderData),
            datasets: [{
                label: 'Volume',
                data: Object.values(orderData),
                backgroundColor: '#d4a373',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
</body>
</html>