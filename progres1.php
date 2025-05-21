<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fanzzervice</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 50px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #0071e3;
        }
        
        .hero {
            height: 500px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/iphone-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 20px;
            margin-bottom: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            background-color: #0071e3;
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #005bb5;
        }
        
        .products {
            padding: 80px 50px;
            text-align: center;
        }
        
        .products h2 {
            font-size: 36px;
            margin-bottom: 50px;
            color: #333;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .product-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
        }
        
        .product-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-info h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .product-info p {
            color: #666;
            margin-bottom: 15px;
        }
        
        .product-info .price {
            font-size: 18px;
            font-weight: bold;
            color: #0071e3;
            margin-bottom: 15px;
        }
        
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 30px;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">Fanzzervice</div>
        <div class="nav-links">
            <a href="index.php">PRODUK</a>
            <a href="kontak.php">KONTAK</a>
        </div>
    </nav>
    
    <section class="hero">
        <h1>Pengen gengsi naik?</h1>
        <p>Sewa iphone disini aja, kami menyediakan berbagai pilihan iphone untuk anda</p>
        <a href="#products" class="btn">Lihat Produk</a>
    </section>
    
    <section class="products" id="products">
        <h2>Produk Yang Tersedia</h2>
        <div class="product-grid">
        <?php
            $query = "SELECT * FROM produk";
            $result = mysqli_query($conn, $query);
            
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<div class="product-card">';
                echo '<img src="images/' . $row['gambar'] . '" alt="' . $row['nama'] . '" class="product-img">';
                echo '<div class="product-info">';
                echo '<h3>' . $row['nama'] . '</h3>';
                echo '<p>' . $row['deskripsi'] . '</p>';
                echo '<div class="price">Rp ' . number_format($row['harga_sewa'], 0, ',', '.') . '/hari</div>';
                echo '<a href="order.php?id=' . $row['id'] . '" class="btn">Sewa Sekarang</a>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </section>
    
    <footer>
        <p>&copy; 2025 Fanzzervice. All Rights Reserved.</p>
    </footer>
</body>
</html>