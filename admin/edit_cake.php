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
        $new_image_path = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $new_image_path)) {
            if (file_exists($image_path)) {
                unlink($image_path); // Delete old image
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
<html>
<head>
    <title>Edit Cake</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h3>Edit Cake</h3>
            <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Cake Name:</label>
                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($cake['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Price:</label>
                    <input type="number" class="form-control" name="price" step="0.01" value="<?= $cake['price'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Current Image:</label><br>
                    <img src="<?= $cake['image'] ?>" alt="Cake Image" width="80"><br><br>
                    <input type="file" name="image" class="form-control-file">
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea class="form-control" name="description" required><?= htmlspecialchars($cake['description']) ?></textarea>
                </div>
                <div class="form-group">
                    <label>Category:</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php
                        $cat_result = $conn->query("SELECT * FROM categories");
                        while ($cat = $cat_result->fetch_assoc()) {
                            $selected = $cat['id'] == $cake['category_id'] ? 'selected' : '';
                            echo "<option value='{$cat['id']}' $selected>{$cat['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="is_available" class="form-check-input" id="available" <?= $cake['is_available'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="available">Available</label>
                </div>
                <button type="submit" class="btn btn-primary">Update Cake</button>
                <a href="view_cakes.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
