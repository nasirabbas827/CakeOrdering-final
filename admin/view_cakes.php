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

    // Get image to delete
    $img_result = $conn->query("SELECT image FROM cakes WHERE cake_id = $delete_id");
    $img_row = $img_result->fetch_assoc();
    if ($img_row && file_exists($img_row['image'])) {
        unlink($img_row['image']); // Delete the image file
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
<html>
<head>
    <title>View Cakes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <h3>All Cakes</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cake Name</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT c.*, cat.name AS category_name FROM cakes c LEFT JOIN categories cat ON c.category_id = cat.id ORDER BY c.cake_id DESC";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['cake_id']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['price']}</td>
                            <td><img src='{$row['image']}' alt='Cake Image' width='60'></td>
                            <td>{$row['description']}</td>
                            <td>{$row['category_name']}</td>
                            <td>" . ($row['is_available'] ? 'Yes' : 'No') . "</td>
                            <td>
                                <a href='edit_cake.php?id={$row['cake_id']}' class='btn btn-warning btn-sm'>Edit</a>
                                <a href='view_cakes.php?delete_id={$row['cake_id']}' onclick=\"return confirm('Are you sure you want to delete this cake?');\" class='btn btn-danger btn-sm'>Delete</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>No cakes found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
