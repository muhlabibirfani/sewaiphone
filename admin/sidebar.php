<?php

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar" id="sidebar">
    <div class="brand">
        <a href="../index.php" class="brand-link">
            <span class="brand-icon"><i class="fas fa-mobile-alt"></i></span>
            <h2>Fanzzervice</h2>
        </a>
    </div>
    
    <div class="menu">
        <ul class="nav">
            <li class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo $current_page == 'orders.php' || $current_page == 'order_detail.php' ? 'active' : ''; ?>">
                <a href="orders.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Pesanan</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo $current_page == 'products.php' || $current_page == 'product_edit.php' || $current_page == 'product_add.php' ? 'active' : ''; ?>">
                <a href="products.php" class="nav-link">
                    <i class="fas fa-box-open"></i>
                    <span>Produk</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo $current_page == 'customers.php' || $current_page == 'customer_detail.php' ? 'active' : ''; ?>">
                <a href="customers.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Pelanggan</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    /* Sidebar Styles */
     .sidebar {
            width: 250px;
            background-color: var(--white);
            box-shadow: var(--shadow);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        
        .sidebar-header h3 {
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: var(--dark);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .sidebar-menu li a:hover, 
        .sidebar-menu li a.active {
            background-color: rgba(0, 113, 227, 0.1);
            color: var(--primary);
        }
        
        .sidebar-menu li a i {
            width: 20px;
            text-align: center;
        }
        
    /* Responsive Sidebar */
    @media (max-width: 768px) {
        .sidebar {
            width: 70px;
            transform: translateX(-70px);
        }
        
        .sidebar.active {
            transform: translateX(0);
            width: 250px;
        }
        
        .sidebar .brand h2 {
            display: none;
        }
        
        .sidebar.active .brand h2 {
            display: block;
        }
        
        .sidebar .nav-link span {
            display: none;
        }
        
        .sidebar.active .nav-link span {
            display: inline;
        }
        
        .main-content {
            margin-left: 0 !important;
        }
    }
    
    /* Main Content Adjustment */
    .main-content {
        margin-left: 250px;
        padding: 20px;
        transition: all 0.3s ease;
    }
    
    /* For when sidebar is collapsed on mobile */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
        }
    }
</style>

<script>
    // Check window size on load and adjust sidebar accordingly
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('active');
        } else {
            sidebar.classList.add('active');
        }
    });
</script>