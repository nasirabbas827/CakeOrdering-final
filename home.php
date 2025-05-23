<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch username
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Handle category filter
$filter_category = $_GET['category'] ?? '';

// Fetch categories
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC"); // Added ORDER BY

// Fetch cakes
if ($filter_category !== '') {
    $cakes_stmt = $conn->prepare("SELECT cakes.*, categories.name AS category_name FROM cakes JOIN categories ON cakes.category_id = categories.id WHERE categories.id = ? ORDER BY cakes.name ASC"); // Added ORDER BY
    $cakes_stmt->bind_param("i", $filter_category);
} else {
    $cakes_stmt = $conn->prepare("SELECT cakes.*, categories.name AS category_name FROM cakes JOIN categories ON cakes.category_id = categories.id ORDER BY cakes.name ASC"); // Added ORDER BY
}
$cakes_stmt->execute();
$cakes_result = $cakes_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Home Page - Cake Shop</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css"> <style>

        .container.mt-5 {
            padding-top: 3rem; /* More space at the top */
            padding-bottom: 3rem; /* More space at the bottom */
        }

        h2, h5, label {
            font-weight: 600;
            color: #212529;
        }

        /* Hero section welcome message */
        .welcome-section {
            background: linear-gradient(to right, #6a11cb 0%, #2575fc 100%); /* Gradient background */
            color: white;
            padding: 3rem 2rem;
            border-radius: 12px;
            margin-bottom: 2.5rem;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        .welcome-section h2 {
            color: white;
            font-size: 2.8rem; /* Larger welcome text */
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        .welcome-section p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Category Filter Form */
        .form-inline .form-control {
            border-radius: 8px; /* Rounded input fields */
            border: 1px solid #ced4da;
            padding: 0.6rem 1rem;
            font-size: 1rem;
        }
        .form-inline .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .form-inline .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            transform: translateY(-2px); /* Slight lift on hover */
        }

        /* Cake Card Styling */
        .card {
            border: none; /* Remove default border */
            border-radius: 12px; /* More rounded corners for cards */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); /* Softer, subtle shadow */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; /* Smooth hover effect */
            overflow: hidden; /* Ensures rounded corners */
        }

        .card:hover {
            transform: translateY(-5px); /* Slight lift on hover */
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15); /* More pronounced shadow on hover */
        }

        .card-img-top {
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            object-fit: cover;
            height: 220px; /* Slightly taller images */
            width: 100%;
        }

        .card-body {
            padding: 1.5rem;
            background-color: #ffffff;
        }

        .card-title {
            font-size: 1.4rem;
            margin-bottom: 0.75rem;
            color: #333;
        }

        .card-text {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #555;
            margin-bottom: 0.5rem;
        }
        .card-text strong {
            color: #212529;
        }

        .card-footer {
            background-color: #fdfdfd;
            border-top: 1px solid #eee;
            padding: 1rem 1.5rem;
            text-align: center;
        }

        /* Button styling inside cards */
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: translateY(-2px);
        }
        .btn-secondary {
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
        }

        /* No cakes message */
        .no-cakes-message {
            text-align: center;
            padding: 4rem 0;
            font-size: 1.2rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); // Your existing navbar include ?>

<div class="container mt-5">
    <div class="welcome-section">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <p>Explore our delightful collection of cakes and make your day sweeter.</p>
    </div>

        <h3>
        Filter Cakes By Category

    </h3>

    <div class="d-flex justify-content-center mb-4">
        <form method="GET" class="form-inline">
            <label for="categoryFilter" class="mr-3 text-nowrap">Filter by Category:</label>
            <select name="category" id="categoryFilter" class="form-control mr-3">
                <option value="">All Categories</option>
                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($filter_category == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-primary">Apply Filter</button>
        </form>
    </div>

        <h3>
        All Available Cakes

    </h3>

    <div class="row">
        <?php
        if ($cakes_result->num_rows > 0):
            while ($cake = $cakes_result->fetch_assoc()):
        ?>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="card h-100">
                    <img src="admin/<?= htmlspecialchars($cake['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($cake['name']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($cake['name']) ?></h5>
                        <p class="card-text text-muted mb-2"><small><?= htmlspecialchars($cake['description']) ?></small></p>
                        <p class="card-text mb-1"><strong>Category:</strong> <span class="badge badge-info"><?= htmlspecialchars($cake['category_name']) ?></span></p>
                        <p class="card-text mb-auto"><strong>Price:</strong> Rs. <?= number_format($cake['price'], 2) ?></p>
                    </div>
                    <div class="card-footer">
                        <?php if ($cake['is_available']): ?>
                            <form action="add_to_cart.php" method="POST">
                                <input type="hidden" name="cake_id" value="<?= $cake['cake_id'] ?>">
                                <button type="submit" class="btn btn-success btn-block">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-block" disabled>Not Available</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php
            endwhile;
        else:
        ?>
        <div class="col-12">
            <p class="no-cakes-message">No cakes found matching your criteria. Please try a different category.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>