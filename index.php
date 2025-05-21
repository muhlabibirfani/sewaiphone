<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fanzzervice | Sewa iPhone</title>
    <meta name="description" content="Sewa iPhone terbaru dengan harga terjangkau. Pengalaman premium tanpa beli device.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #0071e3;
            --primary-dark: #005bb5;
            --secondary: #f5f5f7;
            --dark: #1d1d1f;
            --light: #f5f5f5;
            --gray: #86868b;
            --white: #ffffff;
            --danger: #dc3545;
            --success: #28a745;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Navigation */
        
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo i {
            font-size: 32px;
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--dark);
            cursor: pointer;
        }
        
        .user-greeting {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .btn {
            padding: 10px 20px;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: var(--gray);
        }
        
        .btn-secondary:hover {
            background-color: #6c757d;
        }
        
        .btn-danger {
            background-color: var(--danger);
        }
        
        .btn-success {
            background-color: var(--success);
        }
        
        /* Hero Section */
        .hero {
            height: 80vh;
            min-height: 600px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            text-align: left;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('images/iphone-bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: var(--white);
            padding: 0 50px;
            position: relative;
        }
        
        .hero-content {
            max-width: 600px;
            animation: fadeInUp 1s ease;
        }
        
        .hero h1 {
            font-size: 52px;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 20px;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--white);
            margin-left: 15px;
        }
        
        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* Products Section */
        .section {
            padding: 100px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-title h2 {
            font-size: 36px;
            margin-bottom: 15px;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background-color: var(--primary);
            border-radius: 2px;
        }
        
        .section-title p {
            color: var(--gray);
            max-width: 700px;
            margin: 0 auto;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        
        .product-card {
            background-color: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: var(--primary);
            color: var(--white);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            z-index: 1;
        }
        
        .product-img-container {
            height: 250px;
            overflow: hidden;
            position: relative;
        }
        
        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }
        
        .product-card:hover .product-img {
            transform: scale(1.05);
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-info h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .product-info p {
            color: var(--gray);
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        
        .price {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .stock {
            font-size: 14px;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .stock i {
            color: var(--primary);
        }
        
        /* Features Section */
        .features {
            background-color: var(--secondary);
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .feature-card {
            background-color: var(--white);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 40px;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .feature-card p {
            color: var(--gray);
            font-size: 14px;
        }
        
        /* Footer */
        footer {
            background-color: var(--dark);
            color: var(--white);
            padding: 60px 0 20px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-column h3 {
            font-size: 18px;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background-color: var(--primary);
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 10px;
        }
        
        .footer-column ul li a {
            color: #b3b3b3;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-column ul li a:hover {
            color: var(--primary);
            padding-left: 5px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: var(--white);
            transition: var(--transition);
        }
        
        .social-links a:hover {
            background-color: var(--primary);
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #b3b3b3;
            font-size: 14px;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .hero {
                padding: 0 30px;
                text-align: center;
                align-items: center;
            }
            
            .hero-content {
                text-align: center;
            }
            
            .btn-group {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            
            .btn-outline {
                margin-left: 0;
            }
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            
            .hero h1 {
                font-size: 42px;
            }
            
            .hero p {
                font-size: 18px;
            }
            
            .section {
                padding: 70px 0;
            }
            
            .auth-buttons {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }
            
            .auth-btn {
                width: 100%;
                padding: 12px 20px;
            }
            
            .user-greeting {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px 0;
                border-bottom: 1px solid rgba(0,0,0,0.1);
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .hero {
                height: 70vh;
                min-height: 500px;
                padding: 0 20px;
            }
            
            .hero h1 {
                font-size: 36px;
            }
            
            .section-title h2 {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <section class="hero">
        <div class="hero-content">
            <h1>Pengen Gengsi Naik Tanpa Pinjol?</h1>
            <p>Sewa iphone disini aja, kami menyediakan berbagai pilihan iphone untuk anda. Nikmati pengalaman menggunakan iPhone terbaru tanpa perlu pinjol.</p>
            <div class="btn-group">
                <a href="#products" class="btn">
                    <i class="fas fa-eye"></i> Lihat Produk
                </a>
            </div>
        </div>
    </section>
    
    <section class="section" id="products">
        <div class="container">
            <div class="section-title">
                <h2>Produk Yang Tersedia</h2>
                <p>Pilih iPhone favorit Anda dan nikmati pengalaman premium dengan biaya sewa yang terjangkau</p>
            </div>
            <div class="product-grid">
                <?php
                $query = "SELECT * FROM produk";
                $result = mysqli_query($conn, $query);
                
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="product-card">';
                    if ($row['is_new']) {
                        echo '<span class="product-badge">BARU</span>';
                    }
                    echo '<div class="product-img-container">';
                    echo '<img src="images/' . $row['gambar'] . '" alt="' . $row['nama_produk'] . '" class="product-img">';
                    echo '</div>';
                    echo '<div class="product-info">';
                    echo '<h3>' . $row['nama_produk'] . '</h3>';
                    echo '<p>' . $row['deskripsi'] . '</p>';
                    echo '<div class="product-meta">';
                    echo '<div class="price">Rp ' . number_format($row['harga_sewa'], 0, ',', '.') . '/hari</div>';
                    echo '<div class="stock"><i class="fas fa-mobile-alt"></i> ' . $row['stok_tersedia'] . '/' . $row['stok'] . ' tersedia</div>';
                    echo '</div>';
                    
                    if(isset($_SESSION['user_id'])) {
                        if($row['stok_tersedia'] > 0) {
                            echo '<a href="order.php?id=' . $row['id'] . '" class="btn" style="width: 100%; text-align: center; justify-content: center; margin-top: 15px;">';
                            echo '<i class="fas fa-shopping-cart"></i> Sewa Sekarang';
                            echo '</a>';
                        } else {
                            echo '<button class="btn btn-danger" style="width: 100%; text-align: center; justify-content: center; margin-top: 15px; cursor: not-allowed;" disabled>';
                            echo '<i class="fas fa-times-circle"></i> Stok Habis';
                            echo '</button>';
                        }
                    } else {
                        echo '<button class="btn" style="width: 100%; text-align: center; justify-content: center; margin-top: 15px; opacity: 0.7; cursor: not-allowed;" disabled>';
                        echo '<i class="fas fa-lock"></i> Login untuk Sewa';
                        echo '</button>';
                        echo '<p style="text-align: center; margin-top: 10px; font-size: 12px;">Anda harus login terlebih dahulu</p>';
                    }
                    
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </section>
    
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Fanzzervice</h3>
                    <p>Menyediakan layanan sewa iPhone premium dengan kualitas terbaik dan harga terjangkau.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Produk</h3>
                    <ul>
                        <li><a href="#">iPhone 15 Series</a></li>
                        <li><a href="#">iPhone 14 Series</a></li>
                        <li><a href="#">iPhone 13 Series</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Layanan</h3>
                    <ul>
                        <li><a href="#">Sewa Harian</a></li>
                        <li><a href="#">Sewa Mingguan</a></li>
                        <li><a href="#">Sewa Bulanan</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Kontak</h3>
                    <ul>
                        <li><a href="tel:+6281234567890"><i class="fas fa-phone"></i> +62 812 3456 7890</a></li>
                        <li><a href="mailto:info@fanzzervice.com"><i class="fas fa-envelope"></i> info@fanzzervice.com</a></li>
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> Jakarta, Indonesia</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Fanzzervice. All Rights Reserved. </i></p>
                <!-- | Designed with <i class="fas fa-heart" style="color: #ff0000;"> -->
            </div>
        </div>
    </footer>
    
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
        
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Animation on scroll
        window.addEventListener('scroll', function() {
            const elements = document.querySelectorAll('.product-card, .feature-card');
            const windowHeight = window.innerHeight;
            
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                if (elementPosition < windowHeight - 100) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        });
        
        // Trigger initial animation
        window.dispatchEvent(new Event('scroll'));
    </script>
</body>
</html>