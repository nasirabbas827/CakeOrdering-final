<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="home.php" style="font-family: 'Playfair Display', serif; font-weight: 700; color: #d4a373;">
            <span style="font-size: 1.5rem; letter-spacing: 1px;">BOUTIQUE BAKERY</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto align-items-center">
                <?php if (isset($_SESSION["id"]) && !empty($_SESSION["id"])): ?>
                    <li class="nav-item"><a class="nav-link px-2" href="home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link px-2" href="update_profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link px-2" href="cart.php">Cart</a></li>
                    <li class="nav-item"><a class="nav-link px-2" href="my_orders.php">Orders</a></li>
                    <li class="nav-item"><a class="nav-link px-2" href="custom_requests.php">Custom Requests</a></li>
                    <li class="nav-item ml-lg-2">
                        <a class="btn btn-outline-danger btn-sm rounded-pill px-3" href="logout.php">Logout</a>
                    </li>

                <?php else: ?>
                    <li class="nav-item"><a class="nav-link px-3" href="register.php">Register</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="login.php">Login</a></li>
                    <li class="nav-item ml-lg-3">
                        <a class="btn btn-dark rounded-pill px-4 btn-sm" href="./admin/admin_login.php">Admin Portal</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
    /* Premium Navigation Styling */
    .navbar {
        border-bottom: 1px solid #f2f2f2;
    }
    
    .nav-link {
        font-family: 'Inter', sans-serif;
        font-size: 0.85rem;
        font-weight: 500;
        color: #555 !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .nav-link:hover {
        color: #d4a373 !important;
        transform: translateY(-1px);
    }

    .btn-outline-danger {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    /* Active link state (Optional) */
    .nav-item.active .nav-link {
        color: #d4a373 !important;
        border-bottom: 2px solid #d4a373;
    }

    @media (max-width: 991px) {
        .navbar-nav {
            padding-top: 1rem;
            text-align: center;
        }
        .nav-item {
            padding: 5px 0;
        }
        .btn {
            margin-top: 10px;
            width: 100%;
        }
    }
</style>