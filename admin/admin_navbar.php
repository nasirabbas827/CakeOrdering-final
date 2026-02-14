<style>
    :root {
        --primary-color: #d4a373;
        --accent-color: #faedcd;
        --text-dark: #2d2d2d;
        --bg-light: #fefae0;
        --transition: all 0.3s ease;
    }

    .custom-navbar {
        background-color: #ffffff !important;
        border-bottom: 2px solid var(--accent-color);
        padding: 0.8rem 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .navbar-brand {
        font-family: 'Playfair Display', serif;
        font-weight: 700;
        color: var(--primary-color) !important;
        font-size: 1.5rem;
    }

    .nav-link {
        font-family: 'Inter', sans-serif;
        color: var(--text-dark) !important;
        font-weight: 500;
        margin: 0 5px;
        transition: var(--transition);
        position: relative;
    }

    .nav-link:hover {
        color: var(--primary-color) !important;
    }

    /* Elegant underline hover effect */
    .nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 0;
        background-color: var(--primary-color);
        transition: var(--transition);
    }

    .nav-link:hover::after {
        width: 100%;
    }

    .btn-logout {
        background-color: var(--primary-color);
        color: white !important;
        border-radius: 8px;
        padding: 6px 15px !important;
        margin-left: 10px;
    }

    .btn-logout:hover {
        background-color: #bc8a5f;
        transform: translateY(-2px);
    }

    /* Fix for mobile menu button */
    .navbar-toggler {
        border-color: var(--accent-color);
    }
    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg'%3 Jacques height='32' width='32'%3E%3Cpath stroke='rgba(212, 163, 115, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 8h24M4 16h24M4 24h24'/%3E%3C/svg%3E") !important;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-light custom-navbar sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin_home.php">
        <span style="color: #2d2d2d;">Admin</span> Dashboard
    </a>
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="view_users.php">Users</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="add_categories.php">Categories</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="add_cakes.php">Cakes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin_orders.php">Orders</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin_custom_requests.php">Requests</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin_expenses.php">Expenses</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="reports.php">Reports</a>
        </li>
        <li class="nav-item">
          <a class="nav-link btn-logout" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>