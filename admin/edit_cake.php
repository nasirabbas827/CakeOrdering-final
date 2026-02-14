<?php
session_start();
include('config.php');

// Ensure admin login
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Validate cake_id
if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit;
}

$cake_id = intval($_GET['id']);

// Fetch cake info
$cake_query = $conn->prepare("SELECT * FROM cakes WHERE cake_id = ?");
$cake_query->bind_param("i", $cake_id);
$cake_query->execute();
$cake_result = $cake_query->get_result();

if ($cake_result->num_rows !== 1) {
    echo "Cake not found.";
    exit;
}

$cake = $cake_result->fetch_assoc();

// Update logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    $image_path = $cake['image'];

    if (isset($_FILES['image']) && $_FILES['image']['name']) {
        $target_dir = "uploads/";
        $new_image_path = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $new_image_path)) {
            if (!empty($cake['image']) && file_exists($cake['image'])) {
                unlink($cake['image']); // Delete old image
            }
            $image_path = $new_image_path;
        }
    }

    $update_query = $conn->prepare("UPDATE cakes SET name=?, price=?, image=?, description=?, category_id=?, is_available=? WHERE cake_id=?");
    $update_query->bind_param("sdssiii", $name, $price, $image_path, $description, $category_id, $is_available, $cake_id);

    if ($update_query->execute()) {
        echo "<script>alert('Cake updated successfully.'); window.location.href='view_cakes.php';</script>";
    } else {
        $error = "Failed to update cake.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Cake | Admin Dashboard</title>
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

        .current-img-preview {
            width: 100%;
            max-width: 200px;
            border-radius: 12px;
            border: 3px solid var(--accent-color);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="service-card">
                <div class="row align-items-center mb-4">
                    <div class="col-md-8">
                        <h2>Modify Masterpiece</h2>
                        <p class="text-muted">Updating: <strong><?= htmlspecialchars($cake['name']) ?></strong></p>
                    </div>
                    <div class="col-md-4 text-md-right">
                        <a href="view_cakes.php" class="btn btn-outline-secondary btn-sm">Back to List</a>
                    </div>
                </div>

                <hr>

                <?php if (isset($error)) echo "<div class='alert alert-danger border-0'>$error</div>"; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label class="label-style">Cake Name</label>
                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($cake['name']) ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-style">Price ($)</label>
                                        <input type="number" class="form-control" name="price" step="0.01" value="<?= $cake['price'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-style">Category</label>
                                        <select name="category_id" class="form-control" required>
                                            <?php
                                            $cat_result = $conn->query("SELECT * FROM categories");
                                            while ($cat = $cat_result->fetch_assoc()) {
                                                $selected = $cat['id'] == $cake['category_id'] ? 'selected' : '';
                                                echo "<option value='{$cat['id']}' $selected>{$cat['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="label-style">Description</label>
                                <textarea class="form-control" name="description" rows="5" required><?= htmlspecialchars($cake['description']) ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group text-center">
                                <label class="label-style d-block text-left">Current Visual</label>
                                <img src="<?= $cake['image'] ?>" alt="Cake" class="current-img-preview mb-3">
                                <div class="custom-file text-left">
                                    <input type="file" name="image" class="form-control-file border p-1 rounded">
                                    <small class="text-muted">Upload new image to replace current one</small>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label class="label-style">Availability Status</label>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" class="custom-control-input" id="available" name="is_available" <?= $cake['is_available'] ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="available">Available in Shop</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 border-top pt-4 text-right">
                        <a href="view_cakes.php" class="btn btn-light mr-2">Cancel Changes</a>
                        <button type="submit" class="btn btn-primary px-5 shadow-sm">Update Product</button>
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