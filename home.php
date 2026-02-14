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
    <title>Boutique Bakery | Handcrafted Cakes</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #d4a373;
            --dark: #1a1a1a;
            --cream: #faf9f6;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--cream);
            color: #444;
        }

        /* Luxury Hero Section */
        .welcome-section {
            position: relative;
            background: var(--dark);
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-bottom: 50px;
            overflow: hidden;
        }

        .welcome-section::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('https://images.pexels.com/photos/1120970/pexels-photo-1120970.jpeg') center/cover;
            opacity: 0.4;
        }

        .welcome-section .content {
            position: relative;
            z-index: 2;
            padding: 20px;
        }

        .welcome-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            margin-bottom: 10px;
        }

        .welcome-section p {
            font-weight: 300;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-size: 1rem;
        }

        /* Filter Styling */
        .filter-wrapper {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            margin-top: -80px;
            position: relative;
            z-index: 10;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin-bottom: 30px;
            color: var(--dark);
        }

        /* Product Card Styling */
        .card {
            border: none;
            border-radius: 20px;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            background: white;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 45px rgba(0,0,0,0.1);
        }

        .card-img-top {
            height: 280px;
            object-fit: cover;
            border-radius: 20px 20px 0 0;
        }

        .badge-category {
            background: var(--cream);
            color: var(--gold);
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            padding: 5px 12px;
            border-radius: 50px;
            margin-bottom: 10px;
            display: inline-block;
        }

        .cake-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .price-tag {
            font-weight: 700;
            color: var(--gold);
            font-size: 1.2rem;
        }

        .btn-add {
            background: var(--dark);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-add:hover {
            background: var(--gold);
            color: white;
        }

        .btn-out {
            border-radius: 12px;
            padding: 12px;
            background: #eee;
            color: #999;
            border: none;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="welcome-section">
    <div class="content">
        <p>Est. 2026</p>
        <h2>Welcome, <?= htmlspecialchars($username); ?></h2>
        <p>Your daily dose of sweetness awaits</p>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="filter-wrapper mb-5">
                <form method="GET" class="form-row align-items-center justify-content-center">
                    <div class="col-md-auto mb-2 mb-md-0">
                        <label class="mr-3 font-weight-bold small text-uppercase">Collection</label>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <select name="category" class="form-control custom-select border-0 bg-light">
                            <option value="">All Collections</option>
                            <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($filter_category == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-dark px-4 font-weight-bold">Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <h3 class="section-title text-center">Our Signature Cakes</h3>

    <div class="row">
        <?php if ($cakes_result->num_rows > 0): ?>
            <?php while ($cake = $cakes_result->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6 mb-5">
                    <div class="card h-100 shadow-sm">
                        <img src="admin/<?= htmlspecialchars($cake['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($cake['name']) ?>">
                        <div class="card-body">
                            <span class="badge-category"><?= htmlspecialchars($cake['category_name']) ?></span>
                            <h5 class="cake-name"><?= htmlspecialchars($cake['name']) ?></h5>
                            <p class="small text-muted mb-4"><?= htmlspecialchars($cake['description']) ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="price-tag">Rs. <?= number_format($cake['price'], 2) ?></span>
                                <div>
                                    <?php if ($cake['is_available']): ?>
                                        <form action="add_to_cart.php" method="POST">
                                            <input type="hidden" name="cake_id" value="<?= $cake['cake_id'] ?>">
                                            <button type="submit" class="btn btn-add px-4">Add To Cart</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-out px-3" disabled>Sold Out</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded-lg shadow-sm">
                    <h4 class="text-muted">No cakes found in this collection.</h4>
                    <a href="home.php" class="btn btn-gold mt-3 text-white">View All Cakes</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="py-5 text-center text-muted">
    <small>&copy; 2026 Boutique Bakery System</small>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>