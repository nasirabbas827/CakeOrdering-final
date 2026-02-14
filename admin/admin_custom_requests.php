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
    <meta charset="UTF-8">
    <title>Custom Cake Requests | Admin</title>
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
            background-color: #fafafa;
            color: var(--text-dark);
        }

        h2 { font-family: 'Playfair Display', serif; font-weight: 700; margin-bottom: 25px; }

        .service-card {
            background: #fff;
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 25px;
        }

        /* Status Colors */
        .badge-pending { background-color: #fff3cd; color: #856404; }
        .badge-accepted { background-color: #d4edda; color: #155724; }
        .badge-modifications { background-color: #cce5ff; color: #004085; }
        .badge-rejected { background-color: #f8d7da; color: #721c24; }

        .table thead th {
            background: var(--accent-color);
            border: none;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .design-details-box {
            background: #fdfaf5;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.85rem;
            max-height: 120px;
            overflow-y: auto;
            border-left: 3px solid var(--primary-color);
        }

        .form-control-sm { border-radius: 6px; }

        .btn-save {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border: none;
            transition: var(--transition);
        }

        .btn-save:hover {
            background-color: #bc8a5f;
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container-fluid mt-5">
    <div class="row px-lg-4">
        <div class="col-12">
            <h2>Custom Design Requests</h2>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="service-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Ref ID</th>
                                <th>Customer</th>
                                <th>Design Vision</th>
                                <th>Set Status</th>
                                <th>Admin Feedback / Pricing</th>
                                <th>Date</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows === 0): ?>
                                <tr><td colspan="7" class="text-center py-5">No custom requests yet.</td></tr>
                            <?php else: ?>
                                <?php while ($row = $result->fetch_assoc()): 
                                    $status_label = ($row['status'] === 'suggested_modifications') ? 'Modifications' : $row['status'];
                                ?>
                                    <tr>
                                        <form method="POST">
                                            <td class="font-weight-bold text-muted small">#<?= $row['request_id'] ?></td>
                                            <td>
                                                <div class="font-weight-bold"><?= htmlspecialchars($row['username']) ?></div>
                                            </td>
                                            <td style="width: 300px;">
                                                <div class="design-details-box">
                                                    <?= nl2br(htmlspecialchars($row['design_details'])) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <select name="status" class="form-control form-control-sm border-0 bg-light font-weight-bold">
                                                    <?php
                                                    $statuses = [
                                                        'pending' => 'Pending Review', 
                                                        'accepted' => 'Accepted', 
                                                        'suggested_modifications' => 'Need Changes', 
                                                        'rejected' => 'Rejected'
                                                    ];
                                                    foreach ($statuses as $val => $label) {
                                                        $selected = ($row['status'] === $val) ? "selected" : "";
                                                        echo "<option value='$val' $selected>$label</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                            <td>
                                                <textarea name="admin_feedback" class="form-control form-control-sm" rows="3" placeholder="Add notes or price quote..."><?= htmlspecialchars($row['admin_feedback']) ?></textarea>
                                            </td>
                                            <td class="small text-muted"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                            <td class="text-right align-middle">
                                                <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                                <button type="submit" name="update_request" class="btn btn-save btn-sm px-3">Save Update</button>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endwhile; ?>
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