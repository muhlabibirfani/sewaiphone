<?php
include 'config.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Get order details using prepared statement
$order_query = "SELECT o.*, p.nama_produk, p.gambar, p.harga_sewa 
                FROM orders o 
                JOIN produk p ON o.produk_id = p.id 
                WHERE o.id = ? AND o.user_id = ?";

$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($order_result) == 0) {
    $_SESSION['error'] = "Pesanan tidak ditemukan atau tidak milik Anda";
    header("Location: index.php");
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Calculate DP amount (50% of total price)
$dp_amount = $order['total_harga'] * 0.5;

// Check if any previous payments exist for this order
$existing_payment_query = "SELECT SUM(amount) as total_paid FROM payments WHERE order_id = ?";
$stmt = mysqli_prepare($conn, $existing_payment_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$existing_payment_result = mysqli_stmt_get_result($stmt);
$existing_payment = mysqli_fetch_assoc($existing_payment_result);
$total_paid = $existing_payment['total_paid'] ?? 0;

// Determine remaining amount
$remaining_amount = $order['total_harga'] - $total_paid;

// Process payment confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Validate inputs
        $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
        $payment_proof = isset($_FILES['payment_proof']) ? $_FILES['payment_proof'] : null;
        $transaction_id = isset($_POST['transaction_id']) ? trim($_POST['transaction_id']) : '';
        $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
        $payment_type = isset($_POST['payment_type']) ? trim($_POST['payment_type']) : 'full';
        
        $errors = [];
        
        // Validate payment method
        if (empty($payment_method)) {
            $errors[] = "Metode pembayaran harus dipilih";
        }
        
        // Process file upload for payment proof
        $payment_proof_filename = null;
        if ($payment_method == 'transfer') {
            // Check if file was uploaded
            if (!$payment_proof || $payment_proof['error'] != 0) {
                $errors[] = "Bukti pembayaran harus diunggah untuk transfer bank";
            } else {
                // Validate file type
                $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
                $file_type = $payment_proof['type'];
                
                if (!in_array($file_type, $allowed_types)) {
                    $errors[] = "Format file harus JPG, PNG, atau PDF";
                }
                
                // Validate file size (max 2MB)
                if ($payment_proof['size'] > 2 * 1024 * 1024) {
                    $errors[] = "Ukuran file maksimal 2MB";
                }
                
                // Generate unique filename
                $payment_proof_filename = 'payment_' . $order_id . '_' . time() . '_' . basename($payment_proof['name']);
                $upload_path = 'uploads/payments/' . $payment_proof_filename;
                
                // Move uploaded file
                if (empty($errors)) {
                    if (!move_uploaded_file($payment_proof['tmp_name'], $upload_path)) {
                        $errors[] = "Gagal mengunggah file. Silakan coba lagi.";
                    }
                }
            }
        }
        
        // Validate transaction ID for non-transfer methods
        if ($payment_method != 'transfer' && empty($transaction_id)) {
            $errors[] = "ID Transaksi harus diisi";
        }
        
        // Calculate payment amount based on payment type
        $payment_amount = ($payment_type == 'dp') ? $dp_amount : $remaining_amount;
        
        // If there are errors, throw exception
        if (!empty($errors)) {
            throw new Exception(implode("<br>", $errors));
        }
        
        // Insert payment record
        $payment_query = "INSERT INTO payments (
            order_id, 
            amount, 
            method, 
            status, 
            payment_proof,
            transaction_id,
            payment_date,
            notes,
            payment_type,
            created_at,
            updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, NOW(), NOW())";
        
        $payment_status = ($payment_method == 'transfer') ? 'pending' : 'success';
        
        $stmt = mysqli_prepare($conn, $payment_query);
        mysqli_stmt_bind_param(
            $stmt, 
            "idssssss", 
            $order_id,
            $payment_amount,
            $payment_method,
            $payment_status,
            $payment_proof_filename,
            $transaction_id,
            $notes,
            $payment_type
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal menyimpan pembayaran: " . mysqli_error($conn));
        }
        
        $payment_id = mysqli_insert_id($conn);
        
        // Update order status based on payment method and type
        $update_order = "UPDATE orders SET status = ?, payment_status = ? WHERE id = ?";
        
        if ($payment_type == 'dp') {
            $order_status = ($payment_method == 'transfer') ? 'awaiting_confirmation' : 'processed';
            $payment_status_value = 'partial';
        } else {
            $order_status = ($payment_method == 'transfer') ? 'awaiting_confirmation' : 'processed';
            $payment_status_value = 'paid';
        }
        
        $stmt = mysqli_prepare($conn, $update_order);
        mysqli_stmt_bind_param($stmt, "ssi", $order_status, $payment_status_value, $order_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal memperbarui status pesanan: " . mysqli_error($conn));
        }
        
        // For non-transfer methods, reduce stock immediately
        if ($payment_method != 'transfer') {
            // Update product stock
            $update_stock = "UPDATE produk SET stok_tersedia = stok_tersedia - 1 WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_stock);
            mysqli_stmt_bind_param($stmt, "i", $order['produk_id']);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Gagal memperbarui stok: " . mysqli_error($conn));
            }
            
            // Update stock mutation status
            $update_mutation = "UPDATE stock_mutations SET status = 'confirmed' WHERE order_id = ?";
            $stmt = mysqli_prepare($conn, $update_mutation);
            mysqli_stmt_bind_param($stmt, "i", $order_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Gagal memperbarui mutasi stok: " . mysqli_error($conn));
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Redirect to thank you page
        $_SESSION['success'] = "Pembayaran berhasil diproses!";
        header("Location: payment_success.php?order_id=" . $order_id);
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pesanan | Fanzzervice</title>
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
        .order-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .order-details {
            display: flex;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .product-image {
            width: 120px;
            margin-right: 20px;
        }
        .product-image img {
            width: 100%;
            border-radius: 5px;
        }
        .product-details {
            flex: 1;
            min-width: 280px;
        }
        .product-details h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }
        .dates, .price-details {
            margin-bottom: 15px;
        }
        .price-details .total {
            font-size: 20px;
            font-weight: bold;
            color: #0071e3;
        }
        .payment-section {
            margin-top: 30px;
        }
        .payment-methods {
            margin-bottom: 20px;
        }
        .payment-option {
            display: grid;
            align-items: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .payment-option:hover, .payment-option.selected {
            border-color: #0071e3;
            background-color: #f0f7ff;
        }
        .payment-option.selected {
            border-width: 2px;
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
        .bank-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            display: none;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        input, select, textarea {
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
            width: 100%;
        }
        button:hover {
            background-color: #005bb5;
        }
        .error {
            color: #e74c3c;
            background-color: #fdf5f5;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .payment-type-selector {
            display: flex;
            background-color: #f8f9fa;
            padding: 3px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        .payment-type-option {
            flex: 1;
            text-align: center;
            padding: 12px;
            cursor: pointer;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .payment-type-option.selected {
            background-color: #0071e3;
            color: white;
        }
        .payment-info-box {
            background-color: #e8f4ff;
            border-left: 4px solid #0071e3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        /* Style for the confirmation button */
        .confirmation-button {
            background-color: #0071e3;
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 500;
            width: 100%;
            text-align: center;
            margin-top: 20px;
            transition: background-color 0.2s ease;
        }
        .confirmation-button:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pembayaran Pesanan</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="order-summary">
            <h2>Ringkasan Pesanan</h2>
            <div class="order-details">
                <div class="product-image">
                    <img src="images/<?php echo $order['gambar']; ?>" alt="<?php echo $order['nama_produk']; ?>">
                </div>
                <div class="product-details">
                    <h3><?php echo $order['nama_produk']; ?></h3>
                    <div class="dates">
                        <p><strong>Tanggal Sewa:</strong> <?php echo date('d F Y', strtotime($order['tanggal_sewa'])); ?></p>
                        <p><strong>Tanggal Kembali:</strong> <?php echo date('d F Y', strtotime($order['tanggal_kembali'])); ?></p>
                        <?php
                        // Calculate rental duration
                        $start_date = new DateTime($order['tanggal_sewa']);
                        $end_date = new DateTime($order['tanggal_kembali']);
                        $interval = $start_date->diff($end_date);
                        $days = $interval->days + 1; // Include both start and end dates
                        ?>
                        <p><strong>Durasi:</strong> <?php echo $days; ?> hari</p>
                    </div>
                    <div class="price-details">
                        <p>Harga sewa: Rp <?php echo number_format($order['harga_sewa'], 0, ',', '.'); ?> x <?php echo $days; ?> hari</p>
                        <p class="total">Total: Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="payment-methods">
            <h2>Pilih Jenis Pembayaran</h2>

            <form method="POST" action="payment_succes.php" enctype="multipart/form-data" id="paymentForm">
                <!-- Payment Type Selection -->
                 
                <div class="payment-type-selector">
                    <div class="payment-type-option selected" data-type="full">
                        Bayar Penuh
                    </div>
                    <div class="payment-type-option" data-type="dp">
                        Bayar DP (50%)
                    </div>
                    <input type="hidden" name="payment_type" id="payment_type" value="full">
                </div>
                
                <div class="payment-info-box" id="payment_info">
                    <div id="full_payment_info">
                        <h4>Bayar Penuh</h4>
                        <p>Total pembayaran: <strong>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong></p>
                    </div>
                    <div id="dp_payment_info" style="display: none;">
                        <h4>Bayar DP (50%)</h4>
                        <p>Jumlah DP: <strong>Rp <?php echo number_format($dp_amount, 0, ',', '.'); ?></strong></p>
                        <p>Sisa pembayaran: <strong>Rp <?php echo number_format($order['total_harga'] - $dp_amount, 0, ',', '.'); ?></strong> (dibayar saat pengambilan barang)</p>
                    </div>
                </div>
                
                <h2>Pilih Metode Pembayaran</h2>
                
                <div class="payment-methods">
                    <div class="payment-option" data-method="transfer">
                        <div class="payment-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <div class="payment-details">
                            <h4>Transfer Bank</h4>
                            <p>Transfer melalui ATM, Internet Banking, atau Mobile Banking</p>
                        </div>
                        <input type="radio" name="payment_method" value="transfer" id="method_transfer" style="display:none;" <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] == 'transfer' ? 'checked' : ''; ?>>
                    </div>
                    
                    <div class="payment-option" data-method="qris">
                        <div class="payment-icon">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <div class="payment-details">
                            <h4>QRIS</h4>
                            <p>Scan QRIS menggunakan berbagai e-wallet dan mobile banking</p>
                        </div>
                        <input type="radio" name="payment_method" value="qris" id="method_qris" style="display:none;" <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] == 'qris' ? 'checked' : ''; ?>>
                    </div>
                </div>
                
                <!-- Bank Transfer Details -->
                <div id="bank_transfer_details" class="bank-details" style="<?php echo isset($_POST['payment_method']) && $_POST['payment_method'] == 'transfer' ? 'display: block;' : ''; ?>">
                    <h3>Informasi Rekening</h3>
                    <div class="account-info">
                        <p><strong>Bank:</strong> Bank Negara Indonesia (BNI)</p>
                        <p><strong>Nomor Rekening:</strong> 0123456789</p>
                        <p><strong>Atas Nama:</strong> PT Fanzzervice Indonesia</p>
                        <p><strong>Jumlah:</strong> <span id="transfer_amount">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span></p>
                    </div>
                    
                    <div class="form-group" style="margin-top: 20px;">
                        <label for="payment_proof">Unggah Bukti Transfer</label>
                        <input type="file" name="payment_proof" id="payment_proof" accept="image/jpeg,image/png,image/jpg,application/pdf">
                        <small>Format: JPG, PNG, atau PDF (Maks. 2MB)</small>
                    </div>
                </div>
                <!-- QRIS Details -->
                <div id="qris_details" class="bank-details" style="<?php echo isset($_POST['payment_method']) && $_POST['payment_method'] == 'qris' ? 'display: block;' : ''; ?>">
                    <div style="text-align: center; margin: 20px 0;">
                        <img src="images/qris-placeholder.png" alt="QRIS Code" style="max-width: 250px;">
                        <p style="margin-top: 10px;"><strong>Jumlah:</strong> <span id="qris_amount">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span></p>
                    </div>
                    
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <p style="text-align: center; margin-bottom: 15px;">Scan QRIS code di atas menggunakan aplikasi e-wallet atau mobile banking Anda</p>
                        
                        <div class="form-group">
                            <label for="transaction_id">ID Transaksi QRIS</label>
                            <input type="text" name="transaction_id" id="transaction_id" 
                                placeholder="Contoh: QRIS1234567890ABCD" required
                                style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100%;">
                            <small style="display: block; margin-top: 5px; color: #666;">
                                Masukkan ID transaksi yang muncul setelah pembayaran berhasil
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Catatan Pembayaran (Opsional)</label>
                            <textarea name="notes" id="notes" rows="2" 
                                placeholder="Contoh: Dibayar via DANA/OVO/GoPay/LinkAja"
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; resize: none;"></textarea>
                        </div>
                    </div>
                    
                    <div style="background-color: #e8f4ff; padding: 15px; border-radius: 8px; margin-top: 15px;">
                        <h4 style="margin-top: 0; color: #0066cc;">Petunjuk Pembayaran QRIS:</h4>
                        <ol style="padding-left: 20px; margin-bottom: 0;">
                            <li>Buka aplikasi e-wallet atau mobile banking Anda</li>
                            <li>Pilih menu pembayaran QRIS</li>
                            <li>Scan kode QR di atas</li>
                            <li>Masukkan jumlah yang tertera</li>
                            <li>Konfirmasi pembayaran</li>
                            <li>Catat ID transaksi yang muncul</li>
                        </ol>
                    </div>
                </div>
                
                <!-- Menggunakan input type submit dengan class confirmation-button -->
                <input type="submit" value="Konfirmasi Pembayaran" class="confirmation-button">
            </form>
        </div>
    </div>
    
    <script>
        // Handle payment method selection
        const paymentOptions = document.querySelectorAll('.payment-option');
        
        paymentOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Reset all options
                paymentOptions.forEach(opt => {
                    opt.classList.remove('selected');
                    const radio = opt.querySelector('input[type="radio"]');
                    radio.checked = false;
                });
                
                // Select this option
                this.classList.add('selected');
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                
                // Show relevant payment details
                const method = this.dataset.method;
                
                document.getElementById('bank_transfer_details').style.display = 'none';
                document.getElementById('qris_details').style.display = 'none';
                
                if (method === 'transfer') {
                    document.getElementById('bank_transfer_details').style.display = 'block';
                } else if (method === 'qris') {
                    document.getElementById('qris_details').style.display = 'block';
                }
            });
        });
        
        // Handle payment type selection
        const paymentTypeOptions = document.querySelectorAll('.payment-type-option');
        const fullAmount = <?php echo $order['total_harga']; ?>;
        const dpAmount = <?php echo $dp_amount; ?>;
        
        paymentTypeOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Reset all options
                paymentTypeOptions.forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Select this option
                this.classList.add('selected');
                const type = this.dataset.type;
                document.getElementById('payment_type').value = type;
                
                // Show relevant payment info
                if (type === 'dp') {
                    document.getElementById('full_payment_info').style.display = 'none';
                    document.getElementById('dp_payment_info').style.display = 'block';
                    document.getElementById('transfer_amount').textContent = 'Rp ' + formatNumber(dpAmount);
                    document.getElementById('qris_amount').textContent = 'Rp ' + formatNumber(dpAmount);
                } else {
                    document.getElementById('full_payment_info').style.display = 'block';
                    document.getElementById('dp_payment_info').style.display = 'none';
                    document.getElementById('transfer_amount').textContent = 'Rp ' + formatNumber(fullAmount);
                    document.getElementById('qris_amount').textContent = 'Rp ' + formatNumber(fullAmount);
                }
            });
        });
        
        // Format number with thousands separator
        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }
    </script>
</body>
</html>