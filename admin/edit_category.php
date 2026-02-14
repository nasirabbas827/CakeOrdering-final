<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Get category ID from the query parameter
$category_id = $_GET['id'] ?? null;

if (!$category_id) {
    header("Location: add_categories.php");
    exit;
}

// Fetch the current category details
$query = "SELECT * FROM categories WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    header("Location: add_categories.php");
    exit;
}

// Handle form submission for updating the category
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = trim($_POST['category_name']);

    // Update the category in the database
    $update_query = "UPDATE categories SET name = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $category_name, $category_id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Category updated successfully.');
                window.location.href = 'add_categories.php';
              </script>";
        exit;
    } else {
        $error_message = "Error updating category.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Category | Admin</title>
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
            min-height: 100vh;
        }

        h1, h2, h3, .navbar-brand {
            font-family: 'Playfair Display', serif;
        }

        .service-card {
            border: none;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            padding: 40px;
            margin-top: 50px;
        }

        /* Form Elements */
        .form-control {
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            border-radius: 10px;
            height: auto;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(212, 163, 115, 0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: #bc8a5f;
            transform: translateY(-2px);
        }

        .btn-cancel {
            background-color: transparent;
            color: #888;
            border: 1px solid #e0e0e0;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-cancel:hover {
            background-color: #f8f8f8;
            color: var(--text-dark);
            text-decoration: none;
        }

        .label-caps {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            font-weight: 700;
            color: #888;
            margin-bottom: 8px;
            display: block;
        }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="service-card">
                <div class="text-center mb-4">
                    <h2 class="mb-1">Edit Category</h2>
                    <p class="text-muted small">Update the name for category #<?php echo $category_id; ?></p>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger border-0 small"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="form-group mb-4">
                        <label for="category_name" class="label-caps">Category Name</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" 
                               value="<?php echo htmlspecialchars($category['name']); ?>" required>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="add_categories.php" class="btn-cancel">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>