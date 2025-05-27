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
    <link rel="stylesheet" href="styles/styleindex.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <section class="hero">
        <div class="hero-content">
            <h1>Pengen Gengsi Naik Tanpa Pinjol?</h1>
            <p>Sewa iphone disini aja, kami menyediakan berbagai pilihan iphone untuk anda. Nikmati pengalaman menggunakan iPhone terbaru tanpa perlu pinjol.</p>
            <div class="btn-group">
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