<?php
session_start();
include('config.php');

// Check if the user is an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $price = $_POST['price'];
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    // Handle image upload
    $image_path = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    $insert_query = "INSERT INTO cakes (name, price, image, description, category_id, is_available) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("sdssii", $name, $price, $image_path, $description, $category_id, $is_available);
    
    if ($stmt->execute()) {
        $success = "Cake added successfully!";
    } else {
        $error = "Error adding cake: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Cake | Admin</title>
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            padding: 35px;
            margin-top: 30px;
        }

        .form-control {
            border: 1px solid #e0e0e0;
            padding: 10px 15px;
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
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #bc8a5f;
            transform: translateY(-2px);
        }

        .label-style {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            color: #777;
        }

        .custom-file-upload {
            border: 2px dashed var(--accent-color);
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            background: #fffdf9;
            cursor: pointer;
        }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="service-card">
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h2>Add New Masterpiece</h2>
                        <p class="text-muted">Fill in the details to add a new cake to your collection.</p>
                    </div>
                    <div class="col-md-4 text-md-right">
                        <a href="view_cakes.php" class="btn btn-outline-secondary btn-sm">View All Cakes</a>
                    </div>
                </div>

                <hr>

                <?php if (isset($success)) echo "<div class='alert alert-success border-0'>$success</div>"; ?>
                <?php if (isset($error)) echo "<div class='alert alert-danger border-0'>$error</div>"; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="label-style" for="name">Cake Name</label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Belgian Chocolate Truffle" required>
                            </div>

                            <div class="form-group">
                                <label class="label-style" for="category_id">Category</label>
                                <select name="category_id" id="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php
                                    $cat_query = "SELECT * FROM categories ORDER BY name ASC";
                                    $cat_result = $conn->query($cat_query);
                                    while ($cat = $cat_result->fetch_assoc()) {
                                        echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="label-style" for="price">Price (RS)</label>
                                <input type="number" name="price" id="price" step="0.01" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="label-style" for="image">Cake Image</label>
                                <div class="custom-file-upload">
                                    <input type="file" name="image" id="image" class="form-control-file w-100">
                                    <small class="text-muted">High-quality JPG or PNG recommended</small>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="label-style">Availability</label>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" class="custom-control-input" id="is_available" name="is_available" checked>
                                    <label class="custom-control-label" for="is_available">Available for ordering</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-group">
                                <label class="label-style" for="description">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="4" placeholder="Describe the flavors, layers, and special ingredients..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <button type="submit" class="btn btn-primary px-5 shadow-sm">Save Cake Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>