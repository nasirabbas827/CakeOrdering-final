<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle form submission to add a category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['category_name'])) {
    $category_name = trim($_POST['category_name']);

    $insert_query = "INSERT INTO categories (name) VALUES (?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("s", $category_name);

    if ($stmt->execute()) {
        $category_added = true;
    } else {
        $category_error = "Error adding category: " . $conn->error;
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<script>alert('Category deleted successfully.'); window.location.href = 'add_categories.php';</script>";
    } else {
        echo "<script>alert('Error deleting category.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories | Admin</title>
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
        }

        /* Card & Layout Styling */
        .service-card {
            border: none;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 30px;
            transition: var(--transition);
        }

        /* Form Styling */
        .form-control {
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            border-radius: 10px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(212, 163, 115, 0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: #bc8a5f;
            transform: translateY(-2px);
        }

        /* Table Styling */
        .table thead th {
            background-color: var(--accent-color);
            border: none;
            color: var(--text-dark);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }
        
        .action-link {
            font-size: 0.9rem;
            margin-right: 10px;
            font-weight: 600;
        }
        
        .edit-link { color: #856404; }
        .delete-link { color: #dc3545; }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="service-card sticky-top" style="top: 100px;">
                <h3>Add Category</h3>
                <hr>
                <?php if (isset($category_added)): ?>
                    <div class="alert alert-success">New category added!</div>
                <?php elseif (isset($category_error)): ?>
                    <div class="alert alert-danger"><?php echo $category_error; ?></div>
                <?php endif; ?>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <div class="form-group">
                        <label for="category_name" class="small font-weight-bold">NAME</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" placeholder="e.g. Wedding Cakes" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Save Category</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="service-card">
                <h3 class="mb-4">Existing Categories</h3>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="15%">ID</th>
                                <th>Category Name</th>
                                <th width="25%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM categories ORDER BY id DESC";
                            $result = $conn->query($query);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td class='text-muted'>#{$row['id']}</td>
                                            <td class='font-weight-bold'>{$row['name']}</td>
                                            <td>
                                                <a href='edit_category.php?id={$row['id']}' class='action-link edit-link'>Edit</a>
                                                <a href='add_categories.php?delete_id={$row['id']}' 
                                                   onclick='return confirm(\"Are you sure?\");' 
                                                   class='action-link delete-link'>Delete</a>
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center py-4'>No categories found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>