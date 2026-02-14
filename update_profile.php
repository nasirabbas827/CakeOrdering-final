<?php
include('config.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$success_message = $error_message = "";

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = trim($_POST["username"]);
    $newEmail    = trim($_POST["email"]);
    $newAge      = trim($_POST["age"]);
    $newFullName = trim($_POST["full_name"]);
    $newBio      = trim($_POST["bio"]);

    $update_query = "UPDATE users SET username = ?, email = ?, age = ?, full_name = ?, bio = ? WHERE id = ?";
    
    if ($update_stmt = mysqli_prepare($conn, $update_query)) {
        mysqli_stmt_bind_param($update_stmt, "sssssi", $newUsername, $newEmail, $newAge, $newFullName, $newBio, $user_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success_message = "Your profile has been elegantly updated!";
            $_SESSION["username"] = $newUsername;
        } else {
            $error_message = "We encountered an error. Please try again.";
        }
        mysqli_stmt_close($update_stmt);
    }
}

// Fetch current user details (always fetch fresh data)
$sql = "SELECT username, email, age, full_name, bio FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $username, $email, $age, $full_name, $bio);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Boutique Bakery</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bakery-gold: #d4a373;
            --cream-bg: #faf9f6;
        }
        body { background-color: var(--cream-bg); font-family: 'Inter', sans-serif; }
        .profile-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-top: 50px;
            overflow: hidden;
        }
        .profile-header {
            background: var(--bakery-gold);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .profile-header h2 { font-family: 'Playfair Display', serif; margin: 0; }
        .form-section { padding: 40px; }
        .form-control {
            border-radius: 10px;
            border: 1px solid #eee;
            padding: 12px 15px;
            background: #fdfdfd;
        }
        .form-control:focus {
            border-color: var(--bakery-gold);
            box-shadow: 0 0 0 0.2rem rgba(212, 163, 115, 0.1);
        }
        label { font-weight: 600; color: #555; font-size: 0.9rem; margin-bottom: 8px; }
        .btn-update {
            background: var(--bakery-gold);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
            width: 100%;
        }
        .btn-update:hover {
            background: #bc8a5f;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="profile-card">
                <div class="profile-header">
                    <h2>Member Profile</h2>
                    <p class="mb-0 opacity-75 small">Manage your personal bakery preferences</p>
                </div>

                <div class="form-section">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success border-0 shadow-sm rounded-pill text-center"><?= $success_message ?></div>
                    <?php endif; ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger border-0 shadow-sm rounded-pill text-center"><?= $error_message ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label>Full Name</label>
                                <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($full_name); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Username</label>
                                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($username); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Age</label>
                                <input type="number" class="form-control" name="age" value="<?= htmlspecialchars($age); ?>" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Email Address</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email); ?>" required>
                            </div>
                            <div class="col-md-12 mb-4">
                                <label>Your Bio (Tell us your favorite flavors!)</label>
                                <textarea class="form-control" name="bio" rows="4" placeholder="I love chocolate ganache and minimalist designs..."><?= htmlspecialchars($bio); ?></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-update shadow-sm">Save Changes</button>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="home.php" class="text-muted small">← Return to Bakery Shop</a>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>