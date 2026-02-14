<?php
session_start();
include('config.php');

// Check admin login
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully.'); window.location.href='view_users.php';</script>";
    } else {
        echo "<script>alert('Error deleting user.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Users</title>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #d4a373;
            --accent-color: #faedcd;
            --text-dark: #2d2d2d;
            --bg-light: #fefae0;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #fafafa;
            color: var(--text-dark);
        }

        h1, h2, h3, .navbar-brand {
            font-family: 'Playfair Display', serif;
            color: var(--text-dark);
        }

        .service-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: var(--transition);
            background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 20px;
        }

        .table thead th {
            border-top: none;
            background-color: var(--accent-color);
            color: var(--text-dark);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .btn-delete {
            color: #dc3545;
            transition: var(--transition);
            font-weight: 600;
        }

        .btn-delete:hover {
            color: #a71d2a;
            text-decoration: none;
        }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="display-5">User Management</h2>
            <p class="text-muted">Review and manage your registered customers.</p>
        </div>
    </div>

    <div class="service-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Age</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM users ORDER BY id DESC";
                    $result = $conn->query($query);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td class='font-weight-bold'>#{$row['id']}</td>
                                <td>" . htmlspecialchars($row['full_name']) . "</td>
                                <td>" . htmlspecialchars($row['username']) . "</td>
                                <td>" . htmlspecialchars($row['email']) . "</td>
                                <td>" . htmlspecialchars($row['phone']) . "</td>
                                <td>{$row['age']}</td>
                                <td>
                                    <a href='view_users.php?delete_id={$row['id']}' 
                                       class='btn-delete' 
                                       onclick='return confirm(\"Are you sure you want to delete this user?\")'>
                                       Delete
                                    </a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center py-4'>No users found in the database.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>