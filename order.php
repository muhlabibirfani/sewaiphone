<?php
include 'config.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Start transaction for atomic operations
mysqli_begin_transaction($conn);

try {
    // Get product details (no need for FOR UPDATE since we're not modifying stock yet)
    $product_query = "SELECT * FROM produk WHERE id = $product_id";
    $product_result = mysqli_query($conn, $product_query);
    $product = mysqli_fetch_assoc($product_result);

    if (!$product) {
        throw new Exception("Produk tidak ditemukan");
    }

    // Check stock availability (just for information, not locking)
    if($product['stok_tersedia'] <= 0) {
        $_SESSION['error'] = "Maaf, produk ini sedang tidak tersedia";
        header("Location: index.php");
        exit();
    }

    // Get user details
    $user_query = "SELECT * FROM users WHERE id = $user_id";
    $user_result = mysqli_query($conn, $user_query);
    $user = mysqli_fetch_assoc($user_result);

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Validate and sanitize inputs
        $errors = [];
        $tanggal_sewa = isset($_POST['tanggal_sewa']) ? trim($_POST['tanggal_sewa']) : '';
        $tanggal_kembali = isset($_POST['tanggal_kembali']) ? trim($_POST['tanggal_kembali']) : '';
        $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';

        // Validate dates
        if (empty($tanggal_sewa) || empty($tanggal_kembali)) {
            $errors[] = "Tanggal sewa dan tanggal kembali harus diisi";
        } else {
            // Check date formats
            if (!DateTime::createFromFormat('Y-m-d', $tanggal_sewa)) {
                $errors[] = "Format tanggal sewa tidak valid (YYYY-MM-DD)";
            }
            if (!DateTime::createFromFormat('Y-m-d', $tanggal_kembali)) {
                $errors[] = "Format tanggal kembali tidak valid (YYYY-MM-DD)";
            }
            
            // Check date logic
            if (strtotime($tanggal_sewa) < strtotime(date('Y-m-d'))) {
                $errors[] = "Tanggal sewa tidak boleh lebih awal dari hari ini";
            }
            if (strtotime($tanggal_kembali) <= strtotime($tanggal_sewa)) {
                $errors[] = "Tanggal kembali harus setelah tanggal sewa";
            }
        }

        // Check for overlapping reservations for confirmed orders only
        $overlap_query = "SELECT id FROM orders 
                         WHERE produk_id = ? 
                         AND status = 'confirmed' 
                         AND (
                             (? BETWEEN tanggal_sewa AND tanggal_kembali) 
                             OR (? BETWEEN tanggal_sewa AND tanggal_kembali) 
                             OR (tanggal_sewa BETWEEN ? AND ?) 
                             OR (tanggal_kembali BETWEEN ? AND ?)
                         )";
        $stmt = mysqli_prepare($conn, $overlap_query);
        mysqli_stmt_bind_param($stmt, "issssss", 
            $product_id, 
            $tanggal_sewa, 
            $tanggal_kembali,
            $tanggal_sewa, 
            $tanggal_kembali,
            $tanggal_sewa, 
            $tanggal_kembali
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Produk sudah dipesan untuk tanggal yang diminta";
        }

        // If there are errors, throw exception
        if (!empty($errors)) {
            throw new Exception(implode("<br>", $errors));
        }

        // Calculate rental period and total price
        $sewa = new DateTime($tanggal_sewa);
        $kembali = new DateTime($tanggal_kembali);
        $interval = $sewa->diff($kembali);
        $days = $interval->days + 1; // Include both start and end dates
        $total_harga = $days * $product['harga_sewa'];

        // Insert order with 'pending' status (stock won't be reduced yet)
        $insert_query = "INSERT INTO orders (
            produk_id, user_id, tanggal_sewa, tanggal_kembali, 
            total_harga, status, payment_method
        ) VALUES (?, ?, ?, ?, ?, 'confirmed', ?)";
        
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param(
            $stmt, 
            "iissds", 
            $product_id, 
            $user_id,
            $tanggal_sewa,
            $tanggal_kembali,
            $total_harga,
            $payment_method
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal membuat pesanan: " . mysqli_error($conn));
        }

        $new_order_id = mysqli_insert_id($conn);

        // Update product stock
        $update_stock = "UPDATE produk SET stok_tersedia = stok_tersedia - 1 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_stock);
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal memperbarui stok: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);

        // Store order information in session for display in success page
        $_SESSION['order_success'] = [
            'order_id' => $new_order_id,
            'product_name' => $product['nama_produk'],
            'tanggal_sewa' => $tanggal_sewa,
            'tanggal_kembali' => $tanggal_kembali,
            'total_harga' => $total_harga
        ];

        // Redirect to success page instead of payment page
        header("Location: order_success.php");
        exit();
    }
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    $_SESSION['error'] = $e->getMessage();
    header("Location: order.php?id=" . $product_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa <?php echo $product['nama']; ?> | Fanzzervice</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0071e3;
            margin-bottom: 20px;
        }
        .product-info {
            display: flex;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .product-image {
            width: 200px;
            margin-right: 20px;
        }
        .product-image img {
            width: 100%;
            border-radius: 10px;
        }
        .product-details {
            flex: 1;
            min-width: 300px;
        }
        .product-details h2 {
            margin-top: 0;
        }
        .price {
            font-size: 24px;
            font-weight: bold;
            color: #0071e3;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }
        button {
            background-color: #0071e3;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
        }
        button:hover {
            background-color: #005bb5;
        }
        .error {
            color: red;
            margin-bottom: 20px;
        }
        .user-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .user-info p {
            margin: 5px 0;
        }
        .stock-info {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stock-available {
            color: #28a745;
            font-weight: 500;
        }
        .payment-methods {
            margin-bottom: 20px;
        }
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }
        .payment-icon {
            margin-right: 15px;
            font-size: 24px;
            width: 40px;
            text-align: center;
        }
        .payment-details {
            flex: 1;
        }
        .payment-details h4 {
            margin: 0 0 5px 0;
        }
        .payment-details p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        @media (max-width: 600px) {
            .product-image {
                width: 100%;
                margin-right: 0;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sewa <?php echo $product['nama_produk']; ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="user-info">
            <h3>Informasi Penyewa</h3>
            <p><strong>Nama:</strong> <?php echo $user['name']; ?></p>
            <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
            <p><strong>Telepon:</strong> <?php echo $user['phone']; ?></p>
            <p><strong>Alamat:</strong> <?php echo $user['address']; ?></p>
        </div>
        
        <div class="product-info">
            <div class="product-image">
                <img src="images/<?php echo $product['gambar']; ?>" alt="<?php echo $product['nama_produk']; ?>">
            </div>
            <div class="product-details">
                <h2><?php echo $product['nama_produk']; ?></h2>
                <p><?php echo $product['deskripsi']; ?></p>
                <div class="price">Rp <?php echo number_format($product['harga_sewa'], 0, ',', '.'); ?> /hari</div>
                <div class="stock-info">
                    Stok tersedia: <span class="stock-available"><?php echo $product['stok_tersedia']; ?></span> dari <?php echo $product['stok']; ?> unit
                </div>
            </div>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="tanggal_sewa">Tanggal Sewa</label>
                <input type="date" id="tanggal_sewa" name="tanggal_sewa" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label for="tanggal_kembali">Tanggal Kembali</label>
                <input type="date" id="tanggal_kembali" name="tanggal_kembali" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <button type="submit">Konfirmasi Pesanan</button>
        </form>
    </div>
    
    <script>
        // Calculate total price when dates change
        document.getElementById('tanggal_sewa').addEventListener('change', updateTotalPrice);
        document.getElementById('tanggal_kembali').addEventListener('change', updateTotalPrice);
        
        function updateTotalPrice() {
            const tanggalSewa = document.getElementById('tanggal_sewa').value;
            const tanggalKembali = document.getElementById('tanggal_kembali').value;
            
            if (tanggalSewa && tanggalKembali) {
                const dateStart = new Date(tanggalSewa);
                const dateEnd = new Date(tanggalKembali);
                
                // Check if dates are valid
                if (dateStart > dateEnd) return;
                
                // Calculate days difference (including both start and end dates)
                const timeDiff = dateEnd.getTime() - dateStart.getTime();
                const daysDiff = Math.floor(timeDiff / (1000 * 3600 * 24)) + 1;
                
                // Get daily price
                const dailyPrice = <?php echo $product['harga_sewa']; ?>;
                const totalPrice = dailyPrice * daysDiff;
                
                // Update price display
                document.querySelector('.price').innerHTML = 
                    `Rp ${numberFormat(dailyPrice)} /hari <br>
                     <span style="font-size: 16px;">Total: Rp ${numberFormat(totalPrice)} untuk ${daysDiff} hari</span>`;
            }
        }
        
        function numberFormat(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>
</body>
</html>