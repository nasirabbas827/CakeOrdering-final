<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$message = "";
$status_type = "info";

// Handle new request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['design_details'])) {
    $design_details = trim($_POST['design_details']);
    if ($design_details === '') {
        $message = "Please provide some details so we can understand your vision.";
        $status_type = "danger";
    } else {
        $stmt = $conn->prepare("INSERT INTO custom_requests (user_id, design_details) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $design_details);
        $stmt->execute();
        $stmt->close();
        $message = "Your vision has been shared with our head pastry chef!";
        $status_type = "success";
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
    <title>Custom Creations | Boutique Bakery</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #d4a373; --dark: #1a1a1a; --cream: #faf9f6; }
        body { background-color: var(--cream); font-family: 'Inter', sans-serif; color: #444; }
        
        .header-title { font-family: 'Playfair Display', serif; color: var(--dark); margin-top: 40px; }
        
        /* Form Section */
        .creation-card { 
            background: white; border: none; border-radius: 25px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.05); padding: 40px; margin-bottom: 50px;
        }
        
        .form-control {
            border-radius: 15px; padding: 20px; border: 1px solid #eee; 
            background: #fdfdfd; transition: 0.3s;
        }
        .form-control:focus { border-color: var(--gold); box-shadow: none; background: #fff; }
        
        .btn-submit { 
            background: var(--dark); color: white; border: none; padding: 15px 40px; 
            border-radius: 50px; font-weight: 700; transition: 0.3s;
        }
        .btn-submit:hover { background: var(--gold); transform: translateY(-2px); }

        /* History Section */
        .request-item {
            background: white; border-radius: 20px; padding: 25px; 
            margin-bottom: 20px; border-left: 5px solid #eee; transition: 0.3s;
        }
        .request-item:hover { transform: scale(1.01); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        
        .status-badge {
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
            padding: 5px 15px; border-radius: 50px; letter-spacing: 1px;
        }
        .pending { background: #fff4e6; color: #d97706; }
        .approved { background: #ecfdf5; color: #059669; border-left-color: #059669; }
        .rejected { background: #fef2f2; color: #dc2626; }

        .feedback-box {
            background: #f8f9fa; border-radius: 12px; padding: 15px;
            margin-top: 15px; border-left: 3px solid var(--gold); font-style: italic;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2 class="header-title text-center">Bespoke Cake Design</h2>
            <p class="text-center text-muted mb-5">Tell us your dream cake idea, and our master bakers will bring it to life.</p>

            <?php if ($message): ?>
                <div class="alert alert-<?= $status_type ?> border-0 shadow-sm rounded-pill text-center mb-4">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="creation-card">
                <form method="POST">
                    <div class="form-group">
                        <label class="font-weight-bold small text-uppercase text-muted mb-3">Your Vision & Requirements</label>
                        <textarea name="design_details" class="form-control" rows="6" 
                                  placeholder="Describe the occasion, preferred flavors, color themes, or any specific imagery you'd like us to include..."></textarea>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-submit shadow">Submit Design for Review</button>
                    </div>
                </form>
            </div>

            <h4 class="mb-4" style="font-family: 'Playfair Display', serif;">Your Design History</h4>
            
            <?php if ($result->num_rows === 0): ?>
                <div class="text-center py-4 bg-white rounded-lg shadow-sm">
                    <p class="text-muted mb-0">No custom designs submitted yet.</p>
                </div>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="request-item shadow-sm">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="text-muted small">Request #<?= $row['request_id'] ?></span>
                                <div class="text-muted" style="font-size: 0.8rem;"><?= date('M d, Y', strtotime($row['created_at'])) ?></div>
                            </div>
                            <span class="status-badge <?= strtolower($row['status']) ?>">
                                <?= $row['status'] ?>
                            </span>
                        </div>
                        
                        <p class="mb-0" style="font-size: 0.95rem; line-height: 1.6;">
                            <?= nl2br(htmlspecialchars($row['design_details'])) ?>
                        </p>

                        <?php if ($row['admin_feedback']): ?>
                            <div class="feedback-box">
                                <small class="font-weight-bold d-block mb-1 text-uppercase" style="letter-spacing: 1px;">Chef's Note:</small>
                                <span class="small"><?= nl2br(htmlspecialchars($row['admin_feedback'])) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>