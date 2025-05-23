<?php
session_start();
include('config.php');

// Admin check
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch totals for summary cards
function fetchCount($conn, $table, $condition = "1") {
    $sql = "SELECT COUNT(*) FROM $table WHERE $condition";
    $result = $conn->query($sql);
    return $result ? $result->fetch_row()[0] : 0;
}

$totalAdmins = fetchCount($conn, "admins");
$totalCakes = fetchCount($conn, "cakes", "is_available=1");
$totalOrders = fetchCount($conn, "orders");
$totalUsers = fetchCount($conn, "users");
$totalExpenses = fetchCount($conn, "expenses");

// Fetch recent actions (limit 5 each)
$recentOrders = $conn->query("SELECT order_id, user_id, total_amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
$recentExpenses = $conn->query("SELECT expense_id, expense_type, amount, expense_date FROM expenses ORDER BY expense_date DESC LIMIT 5");
// Fetch recent custom requests (limit 5)
$recentCustomRequests = $conn->query("SELECT request_id, user_id, design_details, status, created_at FROM custom_requests ORDER BY created_at DESC LIMIT 5");

// For Chart.js: fetch orders count grouped by status for last 30 days
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
    <title>Admin Dashboard</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">


    <style>


        .container {
            max-width: 1300px; /* Slightly wider container */
        }

        h2, h4, h5 {
            font-weight: 600;
            color: #212529;
        }

        /* Card styling */
        .card {
            border: none; /* Remove default border */
            border-radius: 12px; /* More rounded corners */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); /* Softer, larger shadow */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; /* Smooth transition for hover */
            overflow: hidden; /* Ensures rounded corners apply to children */
        }

        .card:hover {
            transform: translateY(-5px); /* Slight lift on hover */
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12); /* Enhanced shadow on hover */
        }

        .summary-card .card-body {
            font-size: 1.4rem; /* Slightly smaller font size */
            font-weight: 600;
            padding: 1.5rem; /* More padding */
            border-radius: 10px; /* Match parent border-radius */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 120px; /* Ensure consistent height */
        }

        .summary-card .card-body span {
            font-size: 2.5rem; /* Larger numbers */
            font-weight: 700;
            margin-top: 0.5rem;
            line-height: 1; /* Adjust line height for numbers */
        }

        /* Color adjustments for summary cards */
        .summary-card .bg-primary { background-color: #007bff !important; } /* Bootstrap primary */
        .summary-card .bg-success { background-color: #28a745 !important; } /* Bootstrap success */
        .summary-card .bg-warning { background-color: #ffc107 !important; color: #343a40 !important;} /* Make text dark for contrast */
        .summary-card .bg-info { background-color: #17a2b8 !important; } /* Bootstrap info */
        .summary-card .bg-danger { background-color: #dc3545 !important; } /* Bootstrap danger */

        /* Recent sections styling */
        .recent-section {
            max-height: 300px; /* Slightly increased max height */
            overflow-y: auto;
            border: 1px solid #e9ecef; /* Lighter border */
            border-radius: 8px; /* Rounded corners */
            background-color: #ffffff;
            padding: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); /* Soft shadow */
        }

        /* Table styling */
        .table-sm th, .table-sm td {
            padding: 0.75rem; /* Increased padding */
            font-size: 0.9rem; /* Slightly larger font */
            vertical-align: middle;
        }

        .table-sm th {
            background-color: #e9ecef; /* Light header background */
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6; /* Stronger bottom border for header */
        }

        .table-sm tbody tr:hover {
            background-color: #f1f3f5; /* Subtle row hover effect */
        }

        .table-sm tbody tr:last-child td {
            border-bottom: none; /* Remove border from last row */
        }

        /* Chart adjustments */
        #ordersStatusChart {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.07);
        }

        /* Custom style for status badges */
        .status-badge {
            padding: 0.3em 0.6em;
            border-radius: 0.25rem;
            font-weight: 500;
            font-size: 0.85em;
            white-space: nowrap;
        }
        .status-badge.processing { background-color: #ffeeba; color: #856404; }
        .status-badge.confirmed { background-color: #cce5ff; color: #004085; }
        .status-badge.dispatched { background-color: #d1ecf1; color: #0c5460; }
        .status-badge.delivered { background-color: #d4edda; color: #155724; }
        .status-badge.rejected { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); // Your existing PHP navbar include ?>

<div class="container mt-4 mb-5">
    <h2 class="mb-4">Admin Dashboard</h2>

    <div class="row text-center mb-4 justify-content-center">
        <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
            <div class="card summary-card">
                <div class="card-body bg-primary text-white rounded">
                    <!-- Removed icon here -->
                    Admins<br />
                    <span><?= $totalAdmins ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
            <div class="card summary-card">
                <div class="card-body bg-success text-white rounded">
                    <!-- Removed icon here -->
                    Total Cakes<br />
                    <span><?= $totalCakes ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
            <div class="card summary-card">
                <div class="card-body bg-warning rounded">
                    <!-- Removed icon here -->
                    Total Orders<br />
                    <span><?= $totalOrders ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
            <div class="card summary-card">
                <div class="card-body bg-info text-white rounded">
                    <!-- Removed icon here -->
                    Users<br />
                    <span><?= $totalUsers ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
            <div class="card summary-card">
                <div class="card-body bg-danger text-white rounded">
                    <!-- Removed icon here -->
                    Expenses<br />
                    <span><?= $totalExpenses ?></span>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-3">Recent Actions</h4>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="mb-0">Recent Orders</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="recent-section">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>User ID</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (isset($recentOrders) && $recentOrders && $recentOrders->num_rows > 0): ?>
                                <?php while ($row = $recentOrders->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['order_id']) ?></td>
                                    <td><?= htmlspecialchars($row['user_id']) ?></td>
                                    <td>
                                        <?php
                                            $statusClass = '';
                                            switch (strtolower($row['status'])) {
                                                case 'processing': $statusClass = 'processing'; break;
                                                case 'confirmed': $statusClass = 'confirmed'; break;
                                                case 'dispatched': $statusClass = 'dispatched'; break;
                                                case 'delivered': $statusClass = 'delivered'; break;
                                                case 'rejected': $statusClass = 'rejected'; break;
                                                default: $statusClass = ''; break;
                                            }
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></span>
                                    </td>
                                    <td>Rs. <?= number_format($row['total_amount'], 2) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">No recent orders to display.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="mb-0">Recent Expenses</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="recent-section">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (isset($recentExpenses) && $recentExpenses && $recentExpenses->num_rows > 0): ?>
                                <?php while ($row = $recentExpenses->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['expense_id']) ?></td>
                                    <td><?= htmlspecialchars($row['expense_type']) ?></td>
                                    <td>Rs. <?= number_format($row['amount'], 2) ?></td>
                                    <td><?= htmlspecialchars($row['expense_date']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">No recent expenses to display.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

   <div class="container mt-4 mb-5">


    <!-- Recent Actions (Orders and Expenses) unchanged -->

    <h4 class="mt-4 mb-3">Orders Status (Last 30 Days) & Recent Custom Requests</h4>
    <div class="row">
        <div class="col-md-6 mb-4">
            
            <div class="card p-3">
                <canvas id="ordersStatusChart" style="height: 300px; width: 100%;"></canvas>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="mb-0">Recent Custom Requests</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="recent-section">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>User ID</th>
                                    <th>Design Details</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($recentCustomRequests && $recentCustomRequests->num_rows > 0): ?>
                                <?php while ($row = $recentCustomRequests->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['request_id']) ?></td>
                                    <td><?= htmlspecialchars($row['user_id']) ?></td>
                                    <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($row['design_details']) ?>">
                                        <?= htmlspecialchars(substr($row['design_details'], 0, 50)) ?><?= strlen($row['design_details']) > 50 ? '...' : '' ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= strtolower(str_replace(' ', '_', $row['status'])) ?>">
                                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $row['status']))) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No recent custom requests to display.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('ordersStatusChart').getContext('2d');

    // Make sure $orderStatusData is properly defined in your PHP before this script runs.
    // For demonstration, I'll use a placeholder if the PHP variable isn't set.
    let orderStatusData = {};
    <?php if (isset($orderStatusData)): ?>
        orderStatusData = <?= json_encode($orderStatusData) ?>;
    <?php else: ?>
        // Placeholder data if $orderStatusData is not available (e.g., for local testing)
        orderStatusData = {
            'Processing': 15,
            'Confirmed': 25,
            'Dispatched': 20,
            'Delivered': 30,
            'Rejected': 5
        };
    <?php endif; ?>

    const data = {
        labels: Object.keys(orderStatusData),
        datasets: [{
            label: 'Orders',
            data: Object.values(orderStatusData),
            backgroundColor: [
                'rgba(255, 206, 86, 0.8)', // processing - yellow
                'rgba(54, 162, 235, 0.8)', // confirmed - blue
                'rgba(75, 192, 192, 0.8)', // dispatched - teal
                'rgba(40, 167, 69, 0.8)',  // delivered - green
                'rgba(220, 53, 69, 0.8)'   // rejected - red
            ],
            borderColor: [
                'rgba(255, 206, 86, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(40, 167, 69, 1)',
                'rgba(220, 53, 69, 1)'
            ],
            borderWidth: 1.5,
            borderRadius: 6 // Add rounded corners to bars
        }]
    };

    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allows chart to fit parent container height
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    cornerRadius: 6,
                    displayColors: false,
                    padding: 10
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false // Hide x-axis grid lines
                    },
                    ticks: {
                        color: '#6c757d'
                    }
                },
                y: {
                    beginAtZero: true,
                    precision: 0,
                    grid: {
                        color: '#e9ecef', // Lighter grid lines
                        drawBorder: false // Hide y-axis border
                    },
                    ticks: {
                        color: '#6c757d'
                    }
                }
            }
        }
    });
</script>

</body>
</html>
