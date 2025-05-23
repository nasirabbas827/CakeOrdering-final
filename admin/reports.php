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

// Fetch total income
$stmt_income = $conn->prepare("SELECT SUM(total_amount) FROM orders WHERE status='confirmed' AND created_at BETWEEN ? AND ?");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Financial Reports & Expenses</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
<link rel="stylesheet" href="../css/style.css">


</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2 class="mb-4 text-center">Financial Reports</h2>

    <form method="GET" class="form-inline justify-content-center mb-5">
        <label for="start_date" class="mr-2">Start Date:</label>
        <input type="date" name="start_date" id="start_date" class="form-control mr-3" value="<?= htmlspecialchars($start_date) ?>" required>
        <label for="end_date" class="mr-2">End Date:</label>
        <input type="date" name="end_date" id="end_date" class="form-control mr-3" value="<?= htmlspecialchars($end_date) ?>" required>
        <button type="submit" class="btn btn-primary px-4">Generate</button> 
        <button onclick="window.print();" class="ml-2 btn btn-primary">Print Report</button>

    </form>

    <div class="row text-center">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">Total Income</div>
                <div class="card-body">
                    <div class="summary-value text-success">Rs. <?= number_format($total_income, 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">Total Expenses</div>
                <div class="card-body">
                    <div class="summary-value text-danger">Rs. <?= number_format($total_expenses, 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">Profit / Loss</div>
                <div class="card-body">
                    <div class="summary-value <?= ($profit_loss >= 0) ? 'profit' : 'loss' ?>">
                        <?= ($profit_loss >= 0 ? 'Profit' : 'Loss') ?>: Rs. <?= number_format(abs($profit_loss), 2) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
