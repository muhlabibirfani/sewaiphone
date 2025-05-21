<?php
include '../config.php';

// Check if user is admin, redirect to login if not
if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

// Validate product ID exists in URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = intval($_GET['id']);

// Get product data with prepared statement
$query = "SELECT * FROM produk WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

// If product not found, redirect
if (!$product) {
    header("Location: products.php");
    exit;
}

$errors = [];
$old_image = $product['gambar'] ?? '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and collect form data
    $product['nama'] = clean_input($_POST['nama'] ?? '');
    $product['deskripsi'] = clean_input($_POST['deskripsi'] ?? '');
    $product['harga'] = clean_input($_POST['harga'] ?? '');
    $product['stok'] = clean_input($_POST['stok'] ?? '');
    $product['stok_tersedia'] = clean_input($_POST['stok_tersedia'] ?? '');
    $product['status'] = clean_input($_POST['status'] ?? 'Regular');
    
    // Validate inputs
    if (empty($product['nama'])) {
        $errors['nama'] = 'Nama produk wajib diisi';
    }
    
    if (!is_numeric($product['harga']) || $product['harga'] <= 0) {
        $errors['harga'] = 'Harga harus berupa angka positif';
    }
    
    if (!is_numeric($product['stok']) || $product['stok'] < 0) {
        $errors['stok'] = 'Stok harus berupa angka positif';
    }
    
    if (!is_numeric($product['stok_tersedia']) || $product['stok_tersedia'] < 0) {
        $errors['stok_tersedia'] = 'Stok tersedia harus berupa angka positif';
    }
    
    if ($product['stok_tersedia'] > $product['stok']) {
        $errors['stok_tersedia'] = 'Stok tersedia tidak boleh lebih dari total stok';
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
            if ($old_image && file_exists('../../images/' . $old_image)) {
                unlink('../../images/' . $old_image);
            }
            
            $file_ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $file_name = 'product_' . time() . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $file_path)) {
                $product['gambar'] = 'products/' . $file_name;
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
                 nama = ?, 
                 deskripsi = ?, 
                 harga = ?, 
                 stok = ?,
                 stok_tersedia = ?,
                 status = ?,
                 gambar = ?,
                 updated_at = NOW()
                 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssdiissi", 
            $product['nama'], 
            $product['deskripsi'], 
            $product['harga'], 
            $product['stok'],
            $product['stok_tersedia'],
            $product['status'],
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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk | Fanzzervice</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            display: inline-flex;
            align-items: center;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .image-preview {
            width: 200px;
            height: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .status-select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h2><i class="fas fa-edit"></i> Edit Produk</h2>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?></div>
                    <span><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></span>
                    <button class="btn btn-sm btn-primary mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="form-container">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="nama">Nama Produk</label>
                            <input type="text" id="nama" name="nama" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['nama'] ?? ''); ?>" required>
                            <?php if(isset($errors['nama'])): ?>
                                <span class="error-message"><?php echo $errors['nama']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" class="form-control" rows="4"><?php echo htmlspecialchars($product['deskripsi'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="two-columns">
                            <div class="form-group">
                                <label for="harga">Harga Sewa (Rp)</label>
                                <input type="number" id="harga" name="harga" class="form-control" 
                                       value="<?php echo htmlspecialchars($product['harga'] ?? ''); ?>" required>
                                <?php if(isset($errors['harga'])): ?>
                                    <span class="error-message"><?php echo $errors['harga']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control status-select">
                                    <option value="Regular" <?php echo ($product['status'] ?? '') === 'Regular' ? 'selected' : ''; ?>>Regular</option>
                                    <option value="Baru" <?php echo ($product['status'] ?? '') === 'Baru' ? 'selected' : ''; ?>>Baru</option>
                                    <option value="Diskon" <?php echo ($product['status'] ?? '') === 'Diskon' ? 'selected' : ''; ?>>Diskon</option>
                                    <option value="Habis" <?php echo ($product['status'] ?? '') === 'Habis' ? 'selected' : ''; ?>>Habis</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="two-columns">
                            <div class="form-group">
                                <label for="stok">Total Stok</label>
                                <input type="number" id="stok" name="stok" class="form-control" 
                                       value="<?php echo htmlspecialchars($product['stok'] ?? ''); ?>" required>
                                <?php if(isset($errors['stok'])): ?>
                                    <span class="error-message"><?php echo $errors['stok']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="stok_tersedia">Stok Tersedia</label>
                                <input type="number" id="stok_tersedia" name="stok_tersedia" class="form-control" 
                                       value="<?php echo htmlspecialchars($product['stok_tersedia'] ?? $product['stok'] ?? ''); ?>" required>
                                <?php if(isset($errors['stok_tersedia'])): ?>
                                    <span class="error-message"><?php echo $errors['stok_tersedia']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="gambar">Gambar Produk</label>
                            <div class="image-preview" id="imagePreview">
                                <?php if (!empty($product['gambar'])): ?>
                                    <img id="previewImage" src="../../images/<?php echo htmlspecialchars($product['gambar']); ?>" alt="Preview Gambar">
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
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
            }
        });
        
        // Image preview
        const imageInput = document.getElementById('gambar');
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
        
        // Ensure stok_tersedia doesn't exceed stok
        const stokInput = document.getElementById('stok');
        const stokTersediaInput = document.getElementById('stok_tersedia');
        
        stokInput.addEventListener('change', function() {
            if (parseInt(stokTersediaInput.value) > parseInt(this.value)) {
                stokTersediaInput.value = this.value;
            }
        });
        
        stokTersediaInput.addEventListener('change', function() {
            if (parseInt(this.value) > parseInt(stokInput.value)) {
                this.value = stokInput.value;
                alert('Stok tersedia tidak boleh melebihi total stok!');
            }
        });
    </script>
</body>
</html>