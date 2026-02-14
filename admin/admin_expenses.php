<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Initialize variables
$expense_id = 0;
$expense_type = '';
$amount = '';
$description = '';
$expense_date = date('Y-m-d');
$edit_mode = false;
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expense_type = trim($_POST['expense_type']);
    $amount = trim($_POST['amount']);
    $description = trim($_POST['description']);
    $expense_date = $_POST['expense_date'];

    if (isset($_POST['expense_id']) && !empty($_POST['expense_id'])) {
        $expense_id = intval($_POST['expense_id']);
        $stmt = $conn->prepare("UPDATE expenses SET expense_type=?, amount=?, description=?, expense_date=? WHERE expense_id=?");
        $stmt->bind_param("sdssi", $expense_type, $amount, $description, $expense_date, $expense_id);
        $message = $stmt->execute() ? "Expense updated successfully." : "Error: " . $conn->error;
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO expenses (expense_type, amount, description, expense_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $expense_type, $amount, $description, $expense_date);
        $message = $stmt->execute() ? "Expense added successfully." : "Error: " . $conn->error;
        $stmt->close();
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM expenses WHERE expense_id = ?");
    $stmt->bind_param("i", $del_id);
    $message = $stmt->execute() ? "Expense deleted successfully." : "Error: " . $conn->error;
    $stmt->close();
}

// Handle Edit request
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE expense_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($expense = $result->fetch_assoc()) {
        extract($expense);
        $edit_mode = true;
    }
    $stmt->close();
}

// Fetch total for summary
$total_res = $conn->query("SELECT SUM(amount) as total FROM expenses");
$total_row = $total_res->fetch_assoc();
$total_spent = $total_row['total'] ?? 0;

$expenses = $conn->query("SELECT * FROM expenses ORDER BY expense_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Tracker | Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #d4a373;
            --accent-color: #faedcd;
            --text-dark: #2d2d2d;
            --bg-light: #fefae0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #fafafa;
            color: var(--text-dark);
        }

        h2, h4 { font-family: 'Playfair Display', serif; font-weight: 700; }

        .service-card {
            background: #fff;
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 25px;
            height: 100%;
        }

        .summary-badge {
            background: var(--bg-light);
            border: 1px dashed var(--primary-color);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .table thead th {
            background-color: var(--accent-color);
            border: none;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-control { border-radius: 8px; border: 1px solid #ddd; }
        
        .btn-primary { 
            background-color: var(--primary-color); 
            border: none; 
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-primary:hover { background-color: #bc8a5f; }

        .expense-type-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--primary-color);
            text-transform: uppercase;
        }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container-fluid mt-5 px-lg-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Financial Tracking</h2>
            <p class="text-muted">Manage your business outflows and overheads.</p>
        </div>
        <div class="col-md-6 text-md-right">
            <div class="summary-badge d-inline-block">
                <span class="small text-muted font-weight-bold uppercase">Total Expenses:</span>
                <h4 class="mb-0 text-dark">Rs. <?= number_format($total_spent, 2) ?></h4>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info border-0 shadow-sm"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="service-card">
                <h4><?= $edit_mode ? 'Edit Details' : 'Log New Expense' ?></h4>
                <hr>
                <form method="POST">
                    <input type="hidden" name="expense_id" value="<?= $expense_id ?>">
                    
                    <div class="form-group">
                        <label class="expense-type-label">Category</label>
                        <input type="text" name="expense_type" class="form-control" placeholder="e.g. Ingredients, Electricity" required value="<?= htmlspecialchars($expense_type) ?>">
                    </div>

                    <div class="form-group">
                        <label class="expense-type-label">Amount (Rs.)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required value="<?= htmlspecialchars($amount) ?>">
                    </div>

                    <div class="form-group">
                        <label class="expense-type-label">Transaction Date</label>
                        <input type="date" name="expense_date" class="form-control" required value="<?= htmlspecialchars($expense_date) ?>">
                    </div>

                    <div class="form-group">
                        <label class="expense-type-label">Notes</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Optional details..."><?= htmlspecialchars($description) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block py-2 mt-3">
                        <?= $edit_mode ? 'Update Record' : 'Save Expense' ?>
                    </button>
                    
                    <?php if ($edit_mode): ?>
                        <a href="admin_expenses.php" class="btn btn-light btn-block btn-sm mt-2">Discard Changes</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="service-card">
                <h4>History</h4>
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th class="text-right">Amount</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($expenses->num_rows > 0): ?>
                                <?php while ($row = $expenses->fetch_assoc()): ?>
                                <tr>
                                    <td class="small text-muted"><?= date('M d, Y', strtotime($row['expense_date'])) ?></td>
                                    <td><strong><?= htmlspecialchars($row['expense_type']) ?></strong></td>
                                    <td class="small"><?= htmlspecialchars($row['description']) ?></td>
                                    <td class="text-right font-weight-bold text-danger">- Rs. <?= number_format($row['amount'], 2) ?></td>
                                    <td class="text-center">
                                        <a href="?edit=<?= $row['expense_id'] ?>" class="btn btn-sm btn-outline-info border-0">Edit</a>
                                        <a href="?delete=<?= $row['expense_id'] ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Delete this record?');">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5">No expense records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>