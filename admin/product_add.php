<?php
require_once '../config.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$errors = [];
$produk = [
    'nama_produk' => '',
    'deskripsi' => '',
    'harga_sewa' => '',
    'stok' => '',
    'stok_tersedia' => '',
    'is_new' => 0,
    'gambar' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic product info
    $produk['nama_produk'] = clean_input($_POST['nama_produk']);
    $produk['deskripsi'] = clean_input($_POST['deskripsi']);
    $produk['harga_sewa'] = clean_input($_POST['harga_sewa']);
    $produk['stok'] = clean_input($_POST['stok']);
    $produk['stok_tersedia'] = $produk['stok']; // Set stok tersedia sama dengan stok awal
    $produk['is_new'] = isset($_POST['is_new']) ? 1 : 0;
    $produk['gambar'] = isset($_FILES['gambar']);

    // Validate inputs
    if (empty($produk['nama_produk'])) {
        $errors['nama_produk'] = 'Nama produk wajib diisi';
    }

    if (!is_numeric($produk['harga_sewa']) || $produk['harga_sewa'] <= 0) {
        $errors['harga_sewa'] = 'Harga harus berupa angka positif';
    }

    if (!is_numeric($produk['stok']) || $produk['stok'] < 0) {
        $errors['stok'] = 'Stok harus berupa angka positif';
    }
    
    // Handle image upload - PERTAHANKAN NAMA FILE ASLI SEPENUHNYA
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $file_type = $_FILES['gambar']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../images/products/';
            
            // Cek dan buat direktori jika belum ada
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    $errors['gambar'] = 'Gagal membuat direktori upload';
                }
            }
            
            // Validasi ukuran file (maksimal 2MB)
            if ($_FILES['gambar']['size'] > 2 * 1024 * 1024) {
                $errors['gambar'] = 'Ukuran file terlalu besar (maksimal 2MB)';
            } else {
                // GUNAKAN NAMA FILE ASLI TANPA MODIFIKASI
                $original_name = $_FILES['gambar']['name'];
                $file_path = $upload_dir . $original_name;
                
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $file_path)) {
                    // Store original filename in database
                    $produk['gambar'] = $original_name;
                } else {
                    $errors['gambar'] = 'Gagal mengunggah gambar';
                }
            }
        } else {
            $errors['gambar'] = 'Format file tidak didukung (hanya JPEG, PNG, GIF)';
        }
    } else {
        $errors['gambar'] = 'Gambar produk wajib diisi';
    }
    
    // If no errors, insert the product
    if (empty($errors)) {
        $query = "INSERT INTO produk (
            nama_produk, 
            deskripsi, 
            harga_sewa, 
            stok, 
            stok_tersedia,
            is_new,
            gambar,
            created_at,
            updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssdiiis", 
            $produk['nama_produk'], 
            $produk['deskripsi'], 
            $produk['harga_sewa'], 
            $produk['stok'],
            $produk['stok_tersedia'],
            $produk['is_new'],
            $produk['gambar']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Produk berhasil ditambahkan!";
            header("Location: products.php");
            exit;
        } else {
            $errors['database'] = "Gagal menambahkan produk: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk | Fanzzervice</title>
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
            border-radius: 5px;
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
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
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
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar would be included here -->
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2>Tambah Produk Baru</h2>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                    <span><?php echo $_SESSION['user_name']; ?></span>
                    <button class="btn btn-sm btn-primary mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
            
            <div class="card">
                <div class="form-container">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="nama_produk">Nama Produk <span class="text-muted">(wajib diisi)</span></label>
                            <input type="text" id="nama_produk" name="nama_produk" class="form-control" 
                                   value="<?php echo htmlspecialchars($produk['nama_produk']); ?>" required>
                            <?php if(isset($errors['nama_produk'])): ?>
                                <span class="error-message"><?php echo $errors['nama_produk']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" class="form-control" rows="4"><?php echo htmlspecialchars($produk['deskripsi']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="harga_sewa">Harga Sewa (Rp) <span class="text-muted">(wajib diisi)</span></label>
                            <input type="number" id="harga_sewa" name="harga_sewa" class="form-control" 
                                   value="<?php echo htmlspecialchars($produk['harga_sewa']); ?>" required>
                            <?php if(isset($errors['harga_sewa'])): ?>
                                <span class="error-message"><?php echo $errors['harga_sewa']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="stok">Stok <span class="text-muted">(wajib diisi)</span></label>
                            <input type="number" id="stok" name="stok" class="form-control" 
                                   value="<?php echo htmlspecialchars($produk['stok']); ?>" required>
                            <?php if(isset($errors['stok'])): ?>
                                <span class="error-message"><?php echo $errors['stok']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="is_new" name="is_new" value="1" <?php echo $produk['is_new'] ? 'checked' : ''; ?>>
                            <label for="is_new">Produk Baru</label>
                        </div>
                        
                        <div class="form-group">
                            <label for="gambar">Gambar Produk <span class="text-muted">(wajib diisi)</span></label>
                            <div class="image-preview" id="imagePreview">
                                <img id="previewImage" src="#" alt="Preview Gambar" style="display:none">
                                <span id="previewText">Pilih gambar untuk melihat preview</span>
                            </div>
                            <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*" required>
                            <small class="text-muted">Format yang didukung: JPEG, PNG, GIF. Maksimal 2MB. Nama file asli akan dipertahankan.</small>
                            <?php if(isset($errors['gambar'])): ?>
                                <span class="error-message"><?php echo $errors['gambar']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-submit">
                                <i class="fas fa-plus-circle"></i> Tambah Produk
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
                // Validasi ukuran file
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                
                previewText.style.display = "none";
                previewImage.style.display = "block";
                
                reader.addEventListener('load', function() {
                    previewImage.setAttribute('src', this.result);
                });
                
                reader.readAsDataURL(file);
            } else {
                previewText.style.display = "block";
                previewImage.style.display = "none";
            }
        });
    </script>
</body>
</html>