<nav class="navbar">
    <div class="container">
        <a href="index.php" class="logo">
            <i class="fas fa-mobile-alt"></i>
            <span class="logo-text">Fanzzervice</span>
        </a>
        
        <div class="responsive-icon">
            <i class="fas fa-bars"></i>
        </div>
        
        <div class="nav-links">
            <a href="index.php">PRODUK</a>
            <a href="kontak.php">KONTAK</a>

            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="user-greeting dropdown">
                    <button class="user-avatar-dropdown">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                        <span><?php echo $_SESSION['user_name']; ?> <i class="fas fa-caret-down"></i></span>
                    </button>
                    <div class="dropdown-menu">
                        <a href="riwayat_pesanan.php"><i class="fas fa-history"></i> Riwayat Pesanan</a>
                        <a href="form_pengembalian.php"><i class="fas fa-undo-alt"></i> Form Pengembalian</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="register.php" class="btn-daftar">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                    <a href="login.php" class="btn-masuk">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
    .navbar {
        background-color: #fff;
        padding: 15px 0;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .navbar .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .logo {
        display: flex;
        align-items: center;
        text-decoration: none;
    }

    .logo i {
        color: #0071e3;
        font-size: 24px;
        margin-right: 10px;
    }

    .logo-text {
        color: #0071e3;
        font-size: 24px;
        font-weight: bold;
    }

    .nav-links {
        display: flex;
        align-items: center;
    }

    .nav-links a {
        text-decoration: none;
        color: #333;
        margin-left: 30px;
        font-weight: 500;
    }

    .nav-links a:hover {
        color: #0071e3;
    }

    .auth-buttons {
        display: flex;
        align-items: center;
        margin-left: 20px;
    }

    .btn-daftar {
        display: flex;
        align-items: center;
        background-color: white;
        color: #0071e3;
        padding: 8px 15px;
        border-radius: 20px;
        text-decoration: none;
        font-weight: bold;
        border: 2px solid #0071e3;
        margin-right: 10px;
    }

    .btn-daftar i, .btn-masuk i {
        margin-right: 5px;
    }

    .btn-masuk {
        display: flex;
        align-items: center;
        background-color: #0071e3;
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        text-decoration: none;
        font-weight: bold;
    }

    .btn-masuk:hover {
        background-color: #005bb5;
    }

    .user-greeting {
        display: flex;
        align-items: center;
        margin-left: 20px;
        position: relative;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        background-color: #0071e3;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 10px;
    }

    .user-avatar-dropdown {
        background: none;
        border: none;
        display: flex;
        align-items: center;
        font-weight: bold;
        cursor: pointer;
        color: #333;
    }

    .user-avatar-dropdown i {
        margin-left: 5px;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        top: 45px;
        right: 0;
        background-color: #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-radius: 10px;
        padding: 10px;
        z-index: 999;
        flex-direction: column;
        min-width: 180px;
    }

    .dropdown-menu a {
        color: #333;
        text-decoration: none;
        padding: 8px 12px;
        display: flex;
        align-items: center;
    }

    .dropdown-menu a i {
        margin-right: 8px;
    }

    .dropdown-menu a:hover {
        background-color: #f0f0f0;
        color: #0071e3;
    }

    .auth-btn {
        display: flex;
        align-items: center;
        padding: 8px 15px;
        border-radius: 20px;
        text-decoration: none;
        font-weight: bold;
        margin-left: 10px;
    }

    .auth-btn i {
        margin-right: 5px;
    }

    .auth-btn-primary {
        background-color: #0071e3;
        color: white;
    }

    .auth-btn-secondary {
        background-color: white;
        color: #0071e3;
        border: 2px solid #0071e3;
    }

    .responsive-icon {
        display: none;
        font-size: 24px;
        cursor: pointer;
        color: #0071e3;
    }

    @media screen and (max-width: 768px) {
        .nav-links {
            display: none;
            position: absolute;
            top: 70px;
            left: 0;
            right: 0;
            flex-direction: column;
            background-color: white;
            padding: 20px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
            z-index: 100;
        }

        .nav-links.active {
            display: flex;
        }

        .nav-links a {
            margin: 10px 0;
        }

        .auth-buttons {
            margin-top: 10px;
            margin-left: 0;
        }

        .user-greeting {
            margin: 10px 0;
        }

        .responsive-icon {
            display: block;
        }
    }
</style>

<script>
    // Responsive navbar toggle
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.responsive-icon').addEventListener('click', function() {
            const navLinks = document.querySelector('.nav-links');
            navLinks.classList.toggle('active');
        });

        // Dropdown toggle
        document.addEventListener('click', function(e) {
            const dropdownBtn = document.querySelector('.user-avatar-dropdown');
            const dropdownMenu = document.querySelector('.dropdown-menu');
            if (dropdownBtn && dropdownBtn.contains(e.target)) {
                dropdownMenu.style.display = dropdownMenu.style.display === 'flex' ? 'none' : 'flex';
            } else {
                dropdownMenu.style.display = 'none';
            }
        });
    });
</script>
