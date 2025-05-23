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
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Insert into cakes table
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
<html>
<head>
    <title>Add Cake</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h3>Add Cake</h3>
            <?php
            if (isset($success)) echo "<p class='text-success'>$success</p>";
            if (isset($error)) echo "<p class='text-danger'>$error</p>";
            ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Cake Name:</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" name="price" id="price" step="0.01" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="image">Cake Image:</label>
                    <input type="file" name="image" id="image" class="form-control-file">
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="category_id">Category:</label>
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

                <div class="form-group form-check">
                    <input type="checkbox" name="is_available" id="is_available" class="form-check-input">
                    <label for="is_available" class="form-check-label">Available</label>
                </div>

                <button type="submit" class="btn btn-primary">Add Cake</button>
                <a class="btn btn-dark" href="view_cakes.php">View Cakes</a>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
