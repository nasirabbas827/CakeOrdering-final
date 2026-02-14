<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Default date range: last 30 days
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Fetch total income (Confirmed and Delivered orders only for accuracy)
$stmt_income = $conn->prepare("SELECT SUM(total_amount) FROM orders WHERE status IN ('confirmed', 'delivered') AND DATE(created_at) BETWEEN ? AND ?");
$stmt_income->bind_param("ss", $start_date, $end_date);
$stmt_income->execute();
$stmt_income->bind_result($total_income);
$stmt_income->fetch();
$stmt_income->close();

$total_income = $total_income ?? 0;

// Fetch total expenses
$stmt_expense = $conn->prepare("SELECT SUM(amount) FROM expenses WHERE expense_date BETWEEN ? AND ?");
$stmt_expense->bind_param("ss", $start_date, $end_date);
$stmt_expense->execute();
$stmt_expense->bind_result($total_expenses);
$stmt_expense->fetch();
$stmt_expense->close();

$total_expenses = $total_expenses ?? 0;

// Calculate profit/loss
$profit_loss = $total_income - $total_expenses;
$margin = ($total_income > 0) ? ($profit_loss / $total_income) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Financial Analysis | Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #d4a373;
            --accent-color: #faedcd;
            --success-color: #2a9d8f;
            --danger-color: #e76f51;
            --text-dark: #2d2d2d;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #fcfcfc;
            color: var(--text-dark);
        }

        h2, .report-title { font-family: 'Playfair Display', serif; font-weight: 700; }

        .service-card {
            background: #fff;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .summary-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
            color: #888;
        }

        .summary-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-top: 5px;
        }

        .profit-text { color: var(--success-color); }
        .loss-text { color: var(--danger-color); }

        .filter-bar {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        .progress { height: 8px; border-radius: 10px; }

        @media print {
            .navbar, .btn, .filter-bar { display: none !important; }
            .container { max-width: 100% !important; margin: 0 !important; }
            .service-card { box-shadow: none; border: 1px solid #eee; }
            .print-header { display: block !important; }
        }

        .print-header { display: none; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="print-header text-center">
        <h1>Boutique Bakery Financial Report</h1>
        <p>Statement Period: <?= date('M d, Y', strtotime($start_date)) ?> to <?= date('M d, Y', strtotime($end_date)) ?></p>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="report-title">Financial Analysis</h2>
            <p class="text-muted">Track your revenue and operational efficiency.</p>
        </div>
        <div class="col-md-6 text-md-right">
            <button onclick="window.print();" class="btn btn-outline-dark px-4 shadow-sm">
                <i class="mr-2">⎙</i> Print Statement
            </button>
        </div>
    </div>

    <div class="filter-bar mb-5">
        <form method="GET" class="form-row align-items-end justify-content-center">
            <div class="col-md-3">
                <label class="small font-weight-bold">START DATE</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold">END DATE</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-block font-weight-bold">Generate</button>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card service-card p-4">
                <span class="summary-label">Total Revenue</span>
                <div class="summary-value text-dark">Rs. <?= number_format($total_income, 2) ?></div>
                <div class="progress mt-3">
                    <div class="progress-bar bg-success" style="width: 100%"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card service-card p-4">
                <span class="summary-label">Operating Expenses</span>
                <div class="summary-value text-dark">Rs. <?= number_format($total_expenses, 2) ?></div>
                <div class="progress mt-3">
                    <div class="progress-bar bg-danger" style="width: <?= ($total_income > 0) ? min(($total_expenses/$total_income)*100, 100) : 0 ?>%"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card service-card p-4">
                <span class="summary-label">Net Performance</span>
                <div class="summary-value <?= ($profit_loss >= 0) ? 'profit-text' : 'loss-text' ?>">
                    Rs. <?= number_format(abs($profit_loss), 2) ?>
                </div>
                <div class="mt-2 small font-weight-bold">
                    Margin: <span class="<?= ($margin >= 0) ? 'text-success' : 'text-danger' ?>"><?= number_format($margin, 1) ?>%</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="alert <?= ($profit_loss >= 0) ? 'alert-success' : 'alert-danger' ?> border-0 p-4 shadow-sm">
                <h5 class="font-weight-bold">Report Summary</h5>
                <p class="mb-0">
                    For the period of <strong><?= date('M d', strtotime($start_date)) ?></strong> to <strong><?= date('M d', strtotime($end_date)) ?></strong>, 
                    your business generated a net <?= ($profit_loss >= 0) ? 'profit' : 'loss' ?> of 
                    <strong>Rs. <?= number_format(abs($profit_loss), 2) ?></strong>. 
                    Your expenses account for <strong><?= number_format(($total_income > 0 ? ($total_expenses/$total_income)*100 : 0), 1) ?>%</strong> of your total revenue.
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>