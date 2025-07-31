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
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// Fetch cakes
if ($filter_category !== '') {
    $cakes_stmt = $conn->prepare("SELECT cakes.*, categories.name AS category_name FROM cakes JOIN categories ON cakes.category_id = categories.id WHERE categories.id = ? ORDER BY cakes.name ASC");
    $cakes_stmt->bind_param("i", $filter_category);
} else {
    $cakes_stmt = $conn->prepare("SELECT cakes.*, categories.name AS category_name FROM cakes JOIN categories ON cakes.category_id = categories.id ORDER BY cakes.name ASC");
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
    <link rel="stylesheet" href="./css/style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .container.mt-5 {
            padding-top: 4rem;
            padding-bottom: 4rem;
        }

        h2, h3, h5, label {
            font-weight: 600;
            color: #212529;
        }

        /* Hero section welcome message */
        .welcome-section {
            position: relative;
            background: linear-gradient(to right, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 4rem 2rem;
            border-radius: 12px;
            margin-bottom: 3rem;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            height:480px;
            background-size: cover;
            background-position: center;
            color: white;

        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('https://images.pexels.com/photos/1120970/pexels-photo-1120970.jpeg');
            background-size: cover;
            background-position: cover;
            opacity: 0.3;
            z-index: 1;
        }

        .welcome-section .content {
            position: relative;
            z-index: 2;
        }

        .welcome-section h2 {
            color: white;
            font-size: 3rem;
            margin-bottom: 0.75rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .welcome-section p {
            font-size: 1.3rem;
            opacity: 0.95;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Category Filter Form */
        .form-inline .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.7rem 1.2rem;
            font-size: 1rem;
            min-width: 200px;
        }

        .form-inline .btn-primary {
            background-color: #ff6f61;
            border-color: #ff6f61;
            border-radius: 8px;
            padding: 0.7rem 2rem;
            font-weight: 500;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .form-inline .btn-primary:hover {
            background-color: #e65b50;
            border-color: #e65b50;
            transform: translateY(-2px);
        }

        /* Cake Card Styling */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }

        .card-img-top {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            object-fit: cover;
            height: 250px;
            width: 100%;
        }

        .card-body {
            padding: 1.75rem;
            background-color: #ffffff;
        }

        .card-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .card-text {
            font-size: 1rem;
            line-height: 1.6;
            color: #555;
            margin-bottom: 0.75rem;
        }

        .card-text strong {
            color: #212529;
        }

        .card-footer {
            background-color: #fdfdfd;
            border-top: 1px solid #eee;
            padding: 1.25rem 1.75rem;
            text-align: center;
        }

        /* Button styling inside cards */
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            border-radius: 8px;
            padding: 0.7rem 2rem;
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
            padding: 0.7rem 2rem;
            font-weight: 500;
        }

        /* No cakes message */
        .no-cakes-message {
            text-align: center;
            padding: 5rem 0;
            font-size: 1.3rem;
            color: #6c757d;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .welcome-section h2 {
                font-size: 2.2rem;
            }

            .welcome-section p {
                font-size: 1.1rem;
            }

            .form-inline .form-control {
                min-width: 100%;
                margin-bottom: 1rem;
            }

            .form-inline .btn-primary {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

    <div class="welcome-section">
        <div class="content">
            <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>Indulge in our exquisite collection of handcrafted cakes, perfect for every occasion.</p>
        </div>
    </div>
<div class="container">

    <h3>Filter Cakes By Category</h3>
    <div class="d-flex justify-content-center mb-4">
        <form method="GET" class="form-inline">
            <label for="categoryFilter" class="mr-3 text-nowrap">Filter by Category:</label>
            <select 
    style=" width: 250px; height: 45px; padding: 10px; font-size: 16px;" 
    name="category" 
    id="categoryFilter" 
    class="form-control mr-3"
>
                <option  value="">All Categories</option>
                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                    <option   value="<?= $cat['id'] ?>" <?= ($filter_category == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-primary">Apply Filter</button>
        </form>
    </div>

    <h3>All Available Cakes</h3>
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