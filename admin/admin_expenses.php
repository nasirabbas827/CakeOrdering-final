<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Initialize variables for form
$expense_id = 0;
$expense_type = '';
$amount = '';
$description = '';
$expense_date = date('Y-m-d');
$edit_mode = false;
$message = '';

// Handle form submission for Add or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expense_type = trim($_POST['expense_type']);
    $amount = trim($_POST['amount']);
    $description = trim($_POST['description']);
    $expense_date = $_POST['expense_date'];

    if (isset($_POST['expense_id']) && !empty($_POST['expense_id'])) {
        // Update existing expense
        $expense_id = intval($_POST['expense_id']);

        $stmt = $conn->prepare("UPDATE expenses SET expense_type=?, amount=?, description=?, expense_date=? WHERE expense_id=?");
        $stmt->bind_param("sdssi", $expense_type, $amount, $description, $expense_date, $expense_id);
        if ($stmt->execute()) {
            $message = "Expense updated successfully.";
        } else {
            $message = "Error updating expense: " . $conn->error;
        }
        $stmt->close();
    } else {
        // Add new expense
        $stmt = $conn->prepare("INSERT INTO expenses (expense_type, amount, description, expense_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $expense_type, $amount, $description, $expense_date);
        if ($stmt->execute()) {
            $message = "Expense added successfully.";
        } else {
            $message = "Error adding expense: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle Delete request
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM expenses WHERE expense_id = ?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $message = "Expense deleted successfully.";
    } else {
        $message = "Error deleting expense: " . $conn->error;
    }
    $stmt->close();
}

// Handle Edit request: load data for editing
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT expense_id, expense_type, amount, description, expense_date FROM expenses WHERE expense_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $expense = $result->fetch_assoc();
        $expense_id = $expense['expense_id'];
        $expense_type = $expense['expense_type'];
        $amount = $expense['amount'];
        $description = $expense['description'];
        $expense_date = $expense['expense_date'];
        $edit_mode = true;
    }
    $stmt->close();
}

// Fetch all expenses
$expenses = $conn->query("SELECT * FROM expenses ORDER BY expense_date DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Expenses</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
<link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Business Expenses Management</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>


            <?= $edit_mode ? 'Edit Expense' : 'Add New Expense' ?>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="expense_id" value="<?= $expense_id ?>">
                <div class="form-group">
                    <label for="expense_type">Expense Type</label>
                    <input type="text" name="expense_type" id="expense_type" class="form-control" required value="<?= htmlspecialchars($expense_type) ?>">
                </div>
                <div class="form-group">
                    <label for="amount">Amount (Rs.)</label>
                    <input type="number" step="0.01" min="0" name="amount" id="amount" class="form-control" required value="<?= htmlspecialchars($amount) ?>">
                </div>
                <div class="form-group">
                    <label for="expense_date">Date</label>
                    <input type="date" name="expense_date" id="expense_date" class="form-control" required value="<?= htmlspecialchars($expense_date) ?>">
                </div>
                <div class="form-group">
                    <label for="description">Description / Notes</label>
                    <textarea name="description" id="description" class="form-control"><?= htmlspecialchars($description) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><?= $edit_mode ? 'Update Expense' : 'Add Expense' ?></button>
                <?php if ($edit_mode): ?>
                    <a href="admin_expenses.php" class="btn btn-secondary ml-2">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    </div>
<div class="container">
    <h4>All Expenses</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Amount (Rs.)</th>
                <th>Date</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($expenses->num_rows > 0): ?>
            <?php while ($row = $expenses->fetch_assoc()): ?>
            <tr>
                <td><?= $row['expense_id'] ?></td>
                <td><?= htmlspecialchars($row['expense_type']) ?></td>
                <td><?= number_format($row['amount'], 2) ?></td>
                <td><?= htmlspecialchars($row['expense_date']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td>
                    <a href="?edit=<?= $row['expense_id'] ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="?delete=<?= $row['expense_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" class="text-center">No expenses found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
