<?php
include('config.php');

// Handle category filter
$filter_category = $_GET['category'] ?? '';

// Fetch categories for the filter
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// Fetch cakes with their category names
$query = "SELECT cakes.*, categories.name AS category_name 
          FROM cakes 
          JOIN categories ON cakes.category_id = categories.id";

if ($filter_category !== '') {
    $query .= " WHERE categories.id = ?";
    $cakes_stmt = $conn->prepare($query . " ORDER BY cakes.name ASC");
    $cakes_stmt->bind_param("i", $filter_category);
} else {
    $cakes_stmt = $conn->prepare($query . " ORDER BY cakes.name ASC");
}

$cakes_stmt->execute();
$cakes_result = $cakes_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique Bakery | Handcrafted Delights</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        :root {
            --gold: #d4a373;
            --dark: #1a1a1a;
            --cream: #faf9f6;
            --soft-text: #666;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--cream);
            color: var(--dark);
            scroll-behavior: smooth;
        }

        h1, h2, h3, .playfair { font-family: 'Playfair Display', serif; }

        /* --- Hero Section --- */
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), 
                        url('https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            height: 80vh;
            display: flex;
            align-items: center;
            color: white;
            text-align: center;
            clip-path: ellipse(150% 100% at 50% 0%);
        }

        .hero-title { font-size: 4rem; font-weight: 800; margin-bottom: 1rem; }
        
        /* --- Filters --- */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-top: -40px;
            position: relative;
            z-index: 10;
        }

        /* --- Cake Cards --- */
        .cake-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            background: white;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        }

        .cake-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .cake-img {
            height: 250px;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .cake-card:hover .cake-img { transform: scale(1.1); }

        .price-tag {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gold);
        }

        .category-badge {
            background: #f0e6db;
            color: #8d6e63;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 5px 12px;
            border-radius: 50px;
        }

        /* --- Custom Section --- */
        .custom-banner {
            background: var(--dark);
            color: white;
            border-radius: 30px;
            padding: 60px;
            overflow: hidden;
        }

        .btn-gold {
            background: var(--gold);
            color: white;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }

        .btn-gold:hover {
            background: #bc8a5f;
            color: white;
            transform: scale(1.05);
        }

        footer { background: var(--dark); color: rgba(255,255,255,0.6); }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<section class="hero-section">
    <div class="container">
        <h1 class="hero-title">Handcrafted Moments</h1>
        <p class="lead mb-4">Baked with obsession, delivered with love.</p>
        <a href="#menu" class="btn btn-gold btn-lg">Explore the Collection</a>
    </div>
</section>

<div class="container" id="menu">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="filter-section">
                <form method="GET" class="row align-items-center">
                    <div class="col-md-8 mb-2 mb-md-0">
                        <select name="category" class="form-control border-0 bg-transparent shadow-none" style="font-weight: 600;">
                            <option value="">All Masterpieces</option>
                            <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($filter_category == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-gold btn-block">Filter Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <?php if ($cakes_result->num_rows > 0): ?>
            <?php while ($cake = $cakes_result->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6 mb-5">
                    <div class="cake-card h-100">
                        <div class="overflow-hidden">
                            <img src="admin/<?= htmlspecialchars($cake['image']) ?>" class="cake-img w-100" alt="<?= htmlspecialchars($cake['name']) ?>">
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="category-badge"><?= htmlspecialchars($cake['category_name']) ?></span>
                                <span class="price-tag">Rs. <?= number_format($cake['price'], 0) ?></span>
                            </div>
                            <h4 class="playfair mb-2"><?= htmlspecialchars($cake['name']) ?></h4>
                            <p class="text-muted small"><?= htmlspecialchars($cake['description']) ?></p>
                            
                            <div class="mt-4">
                                <?php if ($cake['is_available']): ?>
                                    <form action="add_to_cart.php" method="POST">
                                        <input type="hidden" name="cake_id" value="<?= $cake['cake_id'] ?>">
                                        <button type="submit" class="btn btn-outline-dark btn-block btn-sm rounded-pill py-2">
                                            <i class="fas fa-shopping-basket mr-2"></i>Add to Cart
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-light btn-block btn-sm rounded-pill disabled" disabled>Sold Out</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="lead text-muted">We're currently perfecting those recipes. Try another category!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<section class="container my-5">
    <div class="custom-banner shadow-lg">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h2 class="display-4 mb-3">Dreaming of a <br><span style="color:var(--gold)">Unique Design?</span></h2>
                <p class="lead mb-4 opacity-75">From wedding tiers to whimsical birthday creations, our master bakers translate your imagination into edible art.</p>
                <a href="custom_requests.php" class="btn btn-gold px-5 py-3">Start Your Consultation</a>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <img src="https://images.unsplash.com/photo-1535254973040-607b474cb50d?auto=format&fit=crop&w=600&q=80" class="img-fluid rounded-circle shadow" style="border: 10px solid rgba(255,255,255,0.1)" alt="Chef at work">
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container text-center">
        <h2 class="playfair mb-5">Trusted by Cake Lovers</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <i class="fas fa-quote-left mb-3 text-muted"></i>
                <p class="font-italic">"The chocolate ganache was life-changing. Best bakery in town!"</p>
                <h6 class="font-weight-bold">- Sarah J.</h6>
            </div>
            <div class="col-md-4 mb-4">
                <i class="fas fa-quote-left mb-3 text-muted"></i>
                <p class="font-italic">"Ordered a custom wedding cake and it was the highlight of the night."</p>
                <h6 class="font-weight-bold">- Michael C.</h6>
            </div>
            <div class="col-md-4 mb-4">
                <i class="fas fa-quote-left mb-3 text-muted"></i>
                <p class="font-italic">"Fast delivery and the packaging was so elegant."</p>
                <h6 class="font-weight-bold">- Emily R.</h6>
            </div>
        </div>
    </div>
</section>

<footer class="py-5">
    <div class="container text-center">
        <h3 class="playfair text-white mb-4">Boutique Bakery</h3>
        <div class="mb-4">
            <a href="#" class="text-white mx-3"><i class="fab fa-instagram fa-lg"></i></a>
            <a href="#" class="text-white mx-3"><i class="fab fa-facebook fa-lg"></i></a>
            <a href="#" class="text-white mx-3"><i class="fab fa-pinterest fa-lg"></i></a>
        </div>
        <p class="small">&copy; 2026 Boutique Bakery System. All rights reserved.</p>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>