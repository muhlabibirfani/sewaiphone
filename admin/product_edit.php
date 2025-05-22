<?php
require_once '../config.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = intval($_GET['id']);

// Get product data
$query = "SELECT * FROM produk WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: products.php");
    exit;
}

$errors = [];
$old_image = $product['gambar'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product['nama_produk'] = clean_input($_POST['nama_produk']);
    $product['deskripsi'] = clean_input($_POST['deskripsi']);
    $product['harga_sewa'] = clean_input($_POST['harga_sewa']);
    $product['stok'] = clean_input($_POST['stok']);
    $product['stok_tersedia'] = clean_input($_POST['stok_tersedia']);
    $product['is_new'] = isset($_POST['is_new']) ? 1 : 0;
    
    // Validate inputs
    if (empty($product['nama_produk'])) {
        $errors['nama_produk'] = 'Nama produk wajib diisi';
    }
    
    if (!is_numeric($product['harga_sewa']) || $product['harga_sewa'] <= 0) {
        $errors['harga_sewa'] = 'Harga harus berupa angka positif';
    }
    
    if (!is_numeric($product['stok']) || $product['stok'] < 0) {
        $errors['stok'] = 'Stok harus berupa angka positif';
    }
    
    if (!is_numeric($product['stok_tersedia']) || $product['stok_tersedia'] < 0) {
        $errors['stok_tersedia'] = 'Stok tersedia harus berupa angka positif';
    }
    
    // Handle image upload if new image is provided
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['gambar']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../../images/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Delete old image if exists
            if ($old_image && file_exists('../../images/products/' . $old_image)) {
                unlink('../../images/products/' . $old_image);
            }
            
            $file_ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $file_name = 'product_' . time() . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $file_path)) {
                $product['gambar'] = $file_name;
            } else {
                $errors['gambar'] = 'Gagal mengunggah gambar';
            }
        } else {
            $errors['gambar'] = 'Format file tidak didukung (hanya JPEG, PNG, GIF)';
        }
    } else {
        // Keep the old image if no new image is uploaded
        $product['gambar'] = $old_image;
    }
    
    // If no errors, update the product
    if (empty($errors)) {
        $query = "UPDATE produk SET 
                 nama_produk = ?, 
                 deskripsi = ?, 
                 harga_sewa = ?, 
                 stok = ?, 
                 stok_tersedia = ?,
                 is_new = ?,
                 gambar = ?,
                 updated_at = NOW()
                 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssdiiisi", 
            $product['nama_produk'], 
            $product['deskripsi'], 
            $product['harga_sewa'], 
            $product['stok'],
            $product['stok_tersedia'],
            $product['is_new'],
            $product['gambar'],
            $product_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Produk berhasil diperbarui!";
            header("Location: products.php");
            exit;
        } else {
            $errors['database'] = "Gagal memperbarui produk: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk | Fanzzervice</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
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
        
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #fff;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .header h2 {
            margin: 0;
            color: #444;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background-color: #4e73df;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: #4e73df;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .error-message {
            color: #e74a3b;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .image-preview {
            width: 200px;
            height: 200px;
            border: 1px dashed #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            overflow: hidden;
            position: relative;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .text-muted {
            color: #6c757d;
            font-size: 12px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #4e73df;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-box h4 {
            margin-top: 0;
            color: #4e73df;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: 600;
            width: 150px;
        }
        
        .info-value {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .info-row {
                flex-direction: column;
            }
            
            .info-label {
                width: 100%;
                margin-bottom: 5px;
            }
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
                <h2>Edit Produk</h2>
                <div class="user-info">
                </div>
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
                <div class="info-box">
                    <h4>Informasi Produk</h4>
                    <div class="info-row">
                        <div class="info-label">Tanggal Ditambahkan:</div>
                        <div class="info-value"><?php echo date('d M Y', strtotime($product['created_at'])); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Terakhir Diupdate:</div>
                        <div class="info-value"><?php echo date('d M Y', strtotime($product['updated_at'])); ?></div>
                    </div>
                </div>
                
                <div class="form-container">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="nama_produk">Nama Produk</label>
                            <input type="text" id="nama_produk" name="nama_produk" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['nama_produk']); ?>" required>
                            <?php if(isset($errors['nama_produk'])): ?>
                                <span class="error-message"><?php echo $errors['nama_produk']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" class="form-control" rows="4"><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="harga_sewa">Harga Sewa (Rp)</label>
                            <input type="number" id="harga_sewa" name="harga_sewa" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['harga_sewa']); ?>" required>
                            <?php if(isset($errors['harga_sewa'])): ?>
                                <span class="error-message"><?php echo $errors['harga_sewa']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="stok">Total Stok</label>
                            <input type="number" id="stok" name="stok" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['stok']); ?>" required>
                            <?php if(isset($errors['stok'])): ?>
                                <span class="error-message"><?php echo $errors['stok']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="stok_tersedia">Stok Tersedia</label>
                            <input type="number" id="stok_tersedia" name="stok_tersedia" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['stok_tersedia']); ?>" required>
                            <?php if(isset($errors['stok_tersedia'])): ?>
                                <span class="error-message"><?php echo $errors['stok_tersedia']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="is_new" name="is_new" value="1" <?php echo $product['is_new'] ? 'checked' : ''; ?>>
                            <label for="is_new">Tandai sebagai Produk Baru</label>
                        </div>
                        
                        <div class="form-group">
                            <label for="gambar">Gambar Produk</label>
                            <div class="image-preview" id="imagePreview">
                                <?php if (!empty($product['gambar'])): ?>
                                    <img id="previewImage" src="../../images/products/<?php echo $product['gambar']; ?>" alt="Preview Gambar">
                                    <span id="previewText" style="display:none">Pilih gambar untuk melihat preview</span>
                                <?php else: ?>
                                    <img id="previewImage" src="#" alt="Preview Gambar" style="display:none">
                                    <span id="previewText">Pilih gambar untuk melihat preview</span>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar</small>
                            <?php if(isset($errors['gambar'])): ?>
                                <span class="error-message"><?php echo $errors['gambar']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="products.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                        
                        <?php if(isset($errors['database'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $errors['database']; ?>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // Image preview
        const imageInput = document.getElementById('gambar');
        const imagePreview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');
        const previewText = document.getElementById('previewText');
        
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                previewText.style.display = "none";
                previewImage.style.display = "block";
                
                reader.addEventListener('load', function() {
                    previewImage.setAttribute('src', this.result);
                });
                
                reader.readAsDataURL(file);
            } else if (previewImage.getAttribute('src') === "#") {
                previewText.style.display = "block";
                previewImage.style.display = "none";
            }
        });
    </script>
</body>
</html>