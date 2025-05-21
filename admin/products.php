<?php
include '../config.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    
    // First check if product exists
    $check_query = "SELECT id FROM produk WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Delete product
        $delete_query = "DELETE FROM produk WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Produk berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus produk: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Produk tidak ditemukan!";
    }
    
    header("Location: products.php");
    exit;
}

// Get all products
$query = "SELECT * FROM produk ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Define base URL for images
$base_url = "/uas2"; // Sesuaikan dengan base URL website Anda
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk | Fanzzervice</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }
        
        /* Admin container */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            z-index: 100;
            border-right: 1px solid #eee;
        }
        
        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
        }
        
        .sidebar-header i {
            color: #007bff;
            margin-right: 10px;
            font-size: 24px;
        }
        
        .sidebar-header h3 {
            font-size: 22px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 15px 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: 0.2s;
            font-size: 16px;
        }
        
        .sidebar-menu li a:hover {
            background-color: #f8f9fa;
        }
        
        .sidebar-menu li a.active {
            background-color: #e8f3ff;
            color: #007bff;
            border-left: 3px solid #007bff;
        }
        
        .sidebar-menu li a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        
        /* Main content */
        .main-content {
            flex: 1;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }
        
        /* Card */
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Table */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            font-weight: 400;
            color: #212529;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            background-color: transparent;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s, background-color 0.15s, border-color 0.15s;
        }
        
        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .btn-danger {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        /* Product specific */
        .product-image-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .add-product-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
                position: fixed;
                height: 100%;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block !important;
            }
        }
        
        .mobile-menu-btn {
            display: none;
        }
        
        /* Debug info */
        .debug-info {
            font-size: 10px;
            color: #999;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-mobile-alt"></i>
                <h3>Fanzzervice</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="./order.php"><i class="fas fa-shopping-cart"></i> Pesanan</a></li>
                <li><a href="products.php" class="active"><i class="fas fa-mobile"></i> Produk</a></li>
                <li><a href="./logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2>Kelola Produk</h2>
            </div>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Produk</h3>
                    <a href="./product_add.php" class="btn btn-primary add-product-btn">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Nama</th>
                                <th>Harga Sewa</th>
                                <th>Stok</th>
                                <th>Stok Tersedia</th>
                                <th>Status</th>
                                <th>Tanggal Ditambahkan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($result) > 0):
                                while ($produk = mysqli_fetch_assoc($result)): 
                                    // Tentukan path gambar yang benar
                                    $gambar_path = "";
                                    if (!empty($produk['gambar'])) {
                                        // Coba beberapa kemungkinan path
                                        $possible_paths = [
                                            "/images/{$produk['gambar']}",
                                            "/uas2/images/{$produk['gambar']}",
                                            "../images/{$produk['gambar']}",
                                            "../../images/{$produk['gambar']}"
                                        ];
                                        
                                        // Gunakan path relatif terhadap root
                                        $gambar_path = "/uas2/images/{$produk['gambar']}";
                                    }
                            ?>
                            <tr>
                                <td>
                                    <?php if (!empty($produk['gambar'])): ?>
                                        <img src="<?php echo $gambar_path; ?>" alt="<?php echo $produk['nama_produk']; ?>" class="product-image-thumb" onerror="this.onerror=null; this.src='/uas2/images/default-product.jpg'; this.alt='Image Not Found';">
                                        <div class="debug-info">Path: <?php echo $gambar_path; ?></div>
                                    <?php else: ?>
                                        <div style="width:60px; height:60px; background:#eee; display:flex; align-items:center; justify-content:center; border-radius:5px;">
                                            <i class="fas fa-mobile-alt" style="color:#ccc;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $produk['nama_produk']; ?></td>
                                <td>Rp <?php echo number_format($produk['harga_sewa'], 0, ',', '.'); ?></td>
                                <td><?php echo $produk['stok']; ?></td>
                                <td><?php echo $produk['stok_tersedia']; ?></td>
                                <td>
                                    <?php if($produk['is_new'] == 1): ?>
                                        <span style="padding: 3px 8px; background-color: #28a745; color: white; border-radius: 3px; font-size: 12px;">Baru</span>
                                    <?php else: ?>
                                        <span style="padding: 3px 8px; background-color: #6c757d; color: white; border-radius: 3px; font-size: 12px;">Regular</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($produk['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="product_edit.php?id=<?php echo $produk['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="products.php?delete=<?php echo $produk['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">Tidak ada data produk</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // Function to check if images exist
        document.addEventListener('DOMContentLoaded', function() {
            // Log base URL for debugging
            console.log("Base URL: <?php echo $base_url; ?>");
            
            // Add fallback for images that fail to load
            var images = document.querySelectorAll('img.product-image-thumb');
            images.forEach(function(img) {
                img.addEventListener('error', function() {
                    console.log("Image failed to load: " + this.src);
                });
            });
        });
    </script>
</body>
</html>