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
    $delete_id = intval($_GET['delete_id']);

    // Get image to delete safely using prepared statement
    $stmt_img = $conn->prepare("SELECT image FROM cakes WHERE cake_id = ?");
    $stmt_img->bind_param("i", $delete_id);
    $stmt_img->execute();
    $img_result = $stmt_img->get_result();
    $img_row = $img_result->fetch_assoc();
    
    if ($img_row && !empty($img_row['image']) && file_exists($img_row['image'])) {
        unlink($img_row['image']); 
    }

    $delete_query = "DELETE FROM cakes WHERE cake_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<script>alert('Cake deleted successfully.'); window.location.href='view_cakes.php';</script>";
    } else {
        echo "<script>alert('Error deleting cake.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cake Inventory | Admin</title>
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

        h2, h3, .navbar-brand {
            font-family: 'Playfair Display', serif;
        }

        .service-card {
            border: none;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 30px;
        }

        /* Search Bar Styling */
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 15px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #bc8a5f;
        }

        /* Table Aesthetics */
        .table thead th {
            background-color: var(--accent-color);
            border: none;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            color: var(--text-dark);
        }

        .cake-img-thum {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--accent-color);
        }

        .badge-available {
            background-color: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .badge-unavailable {
            background-color: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .action-link {
            font-size: 0.85rem;
            font-weight: 600;
            margin: 0 5px;
        }
        
        .edit-text { color: var(--primary-color); }
        .delete-text { color: #dc3545; }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="m-0">Cake Inventory</h2>
            <p class="text-muted">Manage your products and availability</p>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="add_cakes.php" class="btn btn-primary shadow-sm">+ Add New Cake</a>
        </div>
    </div>

    <div class="service-card py-3">
        <form method="GET" class="form-row align-items-center">
            <div class="col-md-8 my-1">
                <input type="text" name="search" class="form-control" placeholder="Search by ID, Name or Category..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>
            <div class="col-md-2 my-1">
                <button type="submit" class="btn btn-primary btn-block">Search</button>
            </div>
            <div class="col-md-2 my-1">
                <a href="view_cakes.php" class="btn btn-outline-secondary btn-block">Reset</a>
            </div>
        </form>
    </div>

    <div class="service-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Cake Details</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
                    $query = "SELECT c.*, cat.name AS category_name 
                              FROM cakes c 
                              LEFT JOIN categories cat ON c.category_id = cat.id";

                    if (!empty($search)) {
                        $query .= " WHERE c.cake_id LIKE '%$search%' 
                                    OR c.name LIKE '%$search%' 
                                    OR cat.name LIKE '%$search%'";
                    }
                    $query .= " ORDER BY c.cake_id DESC";

                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $status_badge = $row['is_available'] 
                                ? '<span class="badge-available">Available</span>' 
                                : '<span class="badge-unavailable">Out of Stock</span>';
                            
                            $image_src = !empty($row['image']) ? $row['image'] : '../img/default-cake.png';

                            echo "<tr>
                                <td class='text-muted small'>#{$row['cake_id']}</td>
                                <td><img src='{$image_src}' class='cake-img-thum' alt='Cake'></td>
                                <td>
                                    <div class='font-weight-bold'>{$row['name']}</div>
                                    <div class='small text-muted text-truncate' style='max-width: 200px;'>{$row['description']}</div>
                                </td>
                                <td><span class='text-secondary'>{$row['category_name']}</span></td>
                                <td class='font-weight-bold' style='color: var(--primary-color)'>RS " . number_format($row['price'], 2) . "</td>
                                <td>{$status_badge}</td>
                                <td class='text-center'>
                                    <a href='edit_cake.php?id={$row['cake_id']}' class='action-link edit-text'>Edit</a>
                                    <a href='view_cakes.php?delete_id={$row['cake_id']}' 
                                       onclick=\"return confirm('Delete this cake?');\" 
                                       class='action-link delete-text'>Delete</a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center py-5'>No cakes found in your inventory.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>