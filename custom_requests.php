<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$message = "";

// Handle new request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['design_details'])) {
    $design_details = trim($_POST['design_details']);
    if ($design_details === '') {
        $message = "Design details cannot be empty.";
    } else {
        $stmt = $conn->prepare("INSERT INTO custom_requests (user_id, design_details) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $design_details);
        $stmt->execute();
        $stmt->close();
        $message = "Your custom request has been submitted successfully.";
    }
}

// Fetch all requests of this user
$stmt = $conn->prepare("SELECT request_id, design_details, status, admin_feedback, created_at FROM custom_requests WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Custom Cake Requests</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Submit a Custom Cake Request</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-5">
        <div class="form-group">
            <label for="design_details">Describe your design, ingredients, or special notes:</label>
            <textarea name="design_details" id="design_details" class="form-control" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>

    <h3>Your Custom Requests</h3>
    <?php if ($result->num_rows === 0): ?>
        <p>You have not submitted any custom requests yet.</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Request ID</th>
                    <th>Design Details</th>
                    <th>Status</th>
                    <th>Admin Feedback</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['request_id'] ?></td>
                        <td><?= nl2br(htmlspecialchars($row['design_details'])) ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td><?= $row['admin_feedback'] ? nl2br(htmlspecialchars($row['admin_feedback'])) : "<em>No feedback yet</em>" ?></td>
                        <td><?= $row['created_at'] ?></td>
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
