<?php
include('config.php');



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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cake Ordering System</title>
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css"> <style>

    <style>
        :root {
            --primary-color: #ff6b6b;
            --secondary-color: #ff8e8e;
            --dark-color: #333;
            --light-color: #f8f9fa;
        }

        
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0;
            text-align: center;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 25px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .feature-box {
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
            transition: transform 0.3s;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .feature-box:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
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
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 50px;
            color: var(--dark-color);
            position: relative;
            display: inline-block;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            width: 50%;
            height: 4px;
            background-color: var(--primary-color);
            bottom: -10px;
            left: 25%;
        }
        
        .cake-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            margin-bottom: 30px;
        }
        
        .cake-card:hover {
            transform: translateY(-10px);
        }
        
        .cake-img {
            height: 200px;
            object-fit: cover;
        }
        
        .cake-title {
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .cake-price {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 30px 0;
        }
        
        .social-icons a {
            color: white;
            font-size: 1.5rem;
            margin: 0 10px;
            transition: color 0.3s;
        }
        
        .social-icons a:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
<?php include('navbar.php'); // Your existing navbar include ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Sweet Delights Await You</h1>
            <p class="hero-subtitle">Order delicious, handcrafted cakes for any occasion</p>
           
            <a href="login.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-utensils me-2"></i>Order Now
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center section-title">Why Choose Us</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-birthday-cake"></i>
                        </div>
                        <h3>Wide Variety</h3>
                        <p>Choose from our extensive collection of cakes for all occasions and tastes.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-paint-brush"></i>
                        </div>
                        <h3>Custom Designs</h3>
                        <p>Personalize your cake with our custom design options to make it truly special.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3>Fast Delivery</h3>
                        <p>Fresh cakes delivered to your doorstep with our reliable delivery service.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
<div class="container mt-5">
    <h3>Filter Cakes By Category</h3>

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

    <!-- Custom Orders Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="section-title">Custom Cake Orders</h2>
                    <p>Have a special design in mind? Our talented bakers can create a custom cake just for your occasion. Whether it's a birthday, wedding, or any celebration, we'll work with you to bring your vision to life.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Personalized designs</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Special ingredients</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Unique shapes and sizes</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Dietary requirements</li>
                    </ul>
                    <a href="custom.php" class="btn btn-primary mt-3">Request Custom Cake</a>
                </div>
                <div class="col-md-6">
                    <img src="https://images.unsplash.com/photo-1535254973040-607b474cb50d?ixlib=rb-1.2.1&auto=format&fit=crop&w=634&q=80" class="img-fluid rounded shadow" alt="Custom Cake">
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center section-title">What Our Customers Say</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card p-4 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://randomuser.me/api/portraits/women/32.jpg" class="rounded-circle me-3" width="60" alt="Customer">
                            <div>
                                <h5 class="mb-0">Sarah Johnson</h5>
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p class="mb-0">"The birthday cake I ordered was absolutely delicious! It arrived on time and looked exactly like the picture. Will definitely order again!"</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://randomuser.me/api/portraits/men/45.jpg" class="rounded-circle me-3" width="60" alt="Customer">
                            <div>
                                <h5 class="mb-0">Michael Chen</h5>
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                            </div>
                        </div>
                        <p class="mb-0">"The custom cake for our anniversary was stunning! The team really listened to what we wanted and delivered beyond our expectations."</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 mb-4 shadow-sm">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://randomuser.me/api/portraits/women/68.jpg" class="rounded-circle me-3" width="60" alt="Customer">
                            <div>
                                <h5 class="mb-0">Emily Rodriguez</h5>
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p class="mb-0">"I've ordered several cakes from here and they never disappoint. The quality is consistently excellent and the customer service is top-notch."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Order Your Perfect Cake?</h2>
            <p class="lead mb-4">Join thousands of satisfied customers who have made their celebrations sweeter with our cakes.</p>
            <a href="register.php" class="btn btn-light btn-lg me-3">Sign Up Now</a>
            <a href="login.php" class="btn btn-outline-light btn-lg">Login</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-dark text-white">
            <div class="text-center">
                <p class="mb-0">&copy; 2025 Cake Ordering System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>