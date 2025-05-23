<?php
session_start();
include('config.php');

// Check admin login
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_request'])) {
    $request_id = intval($_POST['request_id']);
    $status = $_POST['status'];
    $admin_feedback = trim($_POST['admin_feedback']);

    $stmt = $conn->prepare("UPDATE custom_requests SET status = ?, admin_feedback = ? WHERE request_id = ?");
    $stmt->bind_param("ssi", $status, $admin_feedback, $request_id);
    $stmt->execute();
    $stmt->close();

    $message = "Request #$request_id updated successfully.";
}

// Fetch all custom requests with user info
$query = "SELECT cr.*, u.username FROM custom_requests cr JOIN users u ON cr.user_id = u.id ORDER BY cr.created_at DESC";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin - Manage Custom Requests</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
<link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2>Manage Custom Cake Requests</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($result->num_rows === 0): ?>
        <p>No custom requests found.</p>
    <?php else: ?>
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Request ID</th>
                    <th>Customer</th>
                    <th>Design Details</th>
                    <th>Status</th>
                    <th>Admin Feedback</th>
                    <th>Submitted At</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <form method="POST" class="form-inline">
                            <td><?= $row['request_id'] ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td style="max-width: 250px; white-space: pre-wrap;"><?= htmlspecialchars($row['design_details']) ?></td>
                            <td>
                                <select name="status" class="form-control form-control-sm">
                                    <?php
                                    $statuses = ['pending', 'accepted', 'suggested_modifications', 'rejected'];
                                    foreach ($statuses as $status_option) {
                                        $selected = ($row['status'] === $status_option) ? "selected" : "";
                                        echo "<option value='$status_option' $selected>" . ucfirst($status_option) . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <textarea name="admin_feedback" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($row['admin_feedback']) ?></textarea>
                            </td>
                            <td><?= $row['created_at'] ?></td>
                            <td>
                                <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                <button type="submit" name="update_request" class="btn btn-primary btn-sm">Save</button>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
