<?php
include 'config.php';

// Redirect jika belum login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek jika order_id tersedia
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Ambil detail pesanan
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

// Proses konfirmasi pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
    $payment_proof = isset($_FILES['payment_proof']) ? $_FILES['payment_proof'] : null;
    $transaction_id = isset($_POST['transaction_id']) ? trim($_POST['transaction_id']) : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    
    $errors = [];
    
    // Validasi metode pembayaran
    if (empty($payment_method)) {
        $errors[] = "Metode pembayaran harus dipilih";
    }
    
    // Proses upload bukti pembayaran untuk transfer
    $payment_proof_filename = null;
    if ($payment_method == 'transfer') {
        if (!$payment_proof || $payment_proof['error'] != 0) {
            $errors[] = "Bukti pembayaran harus diunggah untuk transfer bank";
        } else {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
            if (!in_array($payment_proof['type'], $allowed_types)) {
                $errors[] = "Format file harus JPG, PNG, atau PDF";
            }
            
            if ($payment_proof['size'] > 2 * 1024 * 1024) {
                $errors[] = "Ukuran file maksimal 2MB";
            }
            
            $payment_proof_filename = 'payment_' . $order_id . '_' . time() . '_' . basename($payment_proof['name']);
            $upload_path = 'uploads/payments/' . $payment_proof_filename;
            
            if (!move_uploaded_file($payment_proof['tmp_name'], $upload_path)) {
                $errors[] = "Gagal mengunggah file. Silakan coba lagi.";
            }
        }
    }
    
    // Validasi ID transaksi untuk non-transfer
    if ($payment_method != 'transfer' && empty($transaction_id)) {
        $errors[] = "ID Transaksi harus diisi";
    }
    
    if (empty($errors)) {
        // Mulai transaksi
        mysqli_begin_transaction($conn);
        
        try {
            // Insert ke tabel payments
            $payment_query = "INSERT INTO payments (
                order_id, 
                amount, 
                method, 
                status, 
                payment_proof,
                transaction_id,
                payment_date,
                notes,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, NOW(), NOW())";
            
            $payment_status = ($payment_method == 'transfer') ? 'pending' : 'success';
            
            $stmt = mysqli_prepare($conn, $payment_query);
            mysqli_stmt_bind_param(
                $stmt, 
                "idsssss", 
                $order_id,
                $order['total_harga'],
                $payment_method,
                $payment_status,
                $payment_proof_filename,
                $transaction_id,
                $notes
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Gagal menyimpan pembayaran: " . mysqli_error($conn));
            }
            
            // Update status order
            $update_order = "UPDATE orders SET 
                status = 'menunggudikirim'
                WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_order);
            mysqli_stmt_bind_param($stmt, "i", $order_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Gagal memperbarui status pesanan: " . mysqli_error($conn));
            }
            
            // Commit transaksi
            mysqli_commit($conn);
            
            $_SESSION['success'] = "Pembayaran berhasil diproses!";
            header("Location: riwayat_pesanan.php");
            exit();
            
        } catch (Exception $e) {
            // Rollback jika error
            mysqli_rollback($conn);
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
       /* Main Styling */
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f5f7fa;
    margin: 0;
    padding: 0;
    color: #333;
}

.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

h1 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 24px;
    font-weight: 600;
}

h2 {
    color: #2c3e50;
    font-size: 18px;
    margin-bottom: 15px;
    font-weight: 500;
}

/* Order Summary Styling */
.order-summary {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
}

.order-details {
    display: flex;
    align-items: center;
}

.product-image {
    width: 80px;
    height: 80px;
    margin-right: 15px;
    border-radius: 5px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-details h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    font-weight: 500;
}

.price-details {
    margin-top: 5px;
}

.price-details p {
    margin: 0;
    font-size: 14px;
}

.price-details .total {
    font-weight: 600;
    color: #2c3e50;
}

/* Payment Method Selection */
.payment-methods {
    margin-bottom: 20px;
}

.payment-option {
    display: flex;
    align-items: center;
    border: 1px solid #dcdfe6;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 10px;
    background-color: #fff;
    cursor: pointer;
    position: relative;
}

.payment-option.selected {
    border-color: #007bff;
    background-color: #f0f7ff;
}

.payment-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #f0f7ff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.payment-option[data-method="transfer"] .payment-icon {
    color: #2c3e50;
}

.payment-option[data-method="qris"] .payment-icon {
    color: #28a745;
}

.payment-icon i {
    font-size: 20px;
}

.payment-details {
    flex: 1;
}

.payment-details h4 {
    margin: 0 0 5px 0;
    font-size: 16px;
    font-weight: 500;
}

.payment-details p {
    margin: 0;
    font-size: 13px;
    color: #6c757d;
}

.payment-option input[type="radio"] {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
}

/* Bank Details & Form */
.bank-details {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
    display: none;
}

.account-info {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 15px;
}

.account-info p {
    margin: 5px 0;
    font-size: 14px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="file"],
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #dcdfe6;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
}

/* Buttons */
.button-group {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.back-button {
    padding: 12px 20px;
    background-color: #f8f9fa;
    border: 1px solid #dcdfe6;
    border-radius: 6px;
    color: #6c757d;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}

.back-button i {
    margin-right: 5px;
}

.back-button:hover {
    background-color: #e9ecef;
}

.submit-button {
    padding: 12px 20px;
    background-color: #007bff;
    border: none;
    border-radius: 6px;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
}

.submit-button:hover {
    background-color: #0069d9;
}

/* Payment Selection Options */
.payment-selection {
    display: flex;
    margin-bottom: 20px;
}

.payment-selection-option {
    flex: 1;
    padding: 12px;
    text-align: center;
    background-color: #f8f9fa;
    border: 1px solid #dcdfe6;
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
}

.payment-selection-option:first-child {
    border-radius: 6px 0 0 6px;
}

.payment-selection-option:last-child {
    border-radius: 0 6px 6px 0;
}

.payment-selection-option.active {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
}

/* Payment Type */
.payment-type {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
}

/* Special styling for payment type selection from the screenshot */
.payment-type-selection {
    display: flex;
    margin-bottom: 20px;
}

.payment-type-btn {
    flex: 1;
    padding: 12px;
    text-align: center;
    background-color: #fff;
    border: 1px solid #dcdfe6;
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
}

.payment-type-btn:first-child {
    border-radius: 6px 0 0 6px;
    border-right: none;
}

.payment-type-btn:last-child {
    border-radius: 0 6px 6px 0;
    border-left: none;
}

.payment-type-btn.active {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
}

/* Payment method details */
.payment-method-details {
    background-color: #f0f7ff;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
}

.payment-method-option {
    background-color: #fff;
    border: 1px solid #dcdfe6;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    cursor: pointer;
}

.payment-method-option .icon {
    width: 36px;
    margin-right: 10px;
    text-align: center;
}

.payment-method-option .text {
    flex: 1;
}

.payment-method-option .text h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 500;
}

.payment-method-option .text p {
    margin: 5px 0 0;
    font-size: 12px;
    color: #6c757d;
}

/* Error Messages */
.error {
    color: #dc3545;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 6px;
    padding: 10px 15px;
    margin-bottom: 20px;
    font-size: 14px;
}

/* Special styling for the full pay option */
.full-payment-option {
    background-color: #f0f7ff;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
}

.full-payment-option .total {
    font-weight: 600;
    color: #2c3e50;
    font-size: 15px;
    margin: 0;
}

/* Styling for the blue button from the screenshot */
.blue-button {
    display: block;
    width: 100%;
    padding: 12px;
    background-color: #007bff;
    border: none;
    border-radius: 6px;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    margin-top: 20px;
}

.blue-button:hover {
    background-color: #0069d9;
}

/* Styling for product image and details */
.product-item {
    display: flex;
    margin-bottom: 15px;
}

.product-item img {
    width: 80px;
    height: 80px;
    border-radius: 6px;
    object-fit: cover;
    margin-right: 15px;
}

.product-item-details h3 {
    margin: 0 0 5px 0;
    font-size: 16px;
    font-weight: 500;
}

.product-item-details p {
    margin: 0;
    font-size: 14px;
    color: #6c757d;
}

/* Payment section header */
.payment-section-header {
    margin-top: 30px;
    margin-bottom: 15px;
    font-size: 18px;
    font-weight: 500;
    color: #2c3e50;
}
    </style>
</head>
<body>
    <div class="container">
        <h1>Konfirmasi Pembayaran</h1>
        
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
                    <div class="price-details">
                        <p class="total">Total: Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <h2>Metode Pembayaran</h2>
            
            <div class="payment-methods">
                <div class="payment-option" data-method="qris">
                    <div class="payment-icon">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <div class="payment-details">
                        <h4>QRIS</h4>
                        <p>Scan QRIS menggunakan berbagai e-wallet dan mobile banking</p>
                    </div>
                    <input type="radio" name="payment_method" value="qris" id="method_qris">
                </div>
            </div>
            
            <!-- Bank Transfer Details -->
            <div id="bank_transfer_details" class="bank-details">
                <h3>Informasi Rekening</h3>
                <div class="account-info">
                    <p><strong>Bank:</strong> Bank Negara Indonesia (BNI)</p>
                    <p><strong>Nomor Rekening:</strong> 0123456789</p>
                    <p><strong>Atas Nama:</strong> PT Fanzzervice Indonesia</p>
                </div>
                
                <div class="form-group">
                    <label for="payment_proof">Unggah Bukti Transfer</label>
                    <input type="file" name="payment_proof" id="payment_proof" accept="image/jpeg,image/png,image/jpg,application/pdf">
                </div>
            </div>
            
            <!-- QRIS Details -->
            <div id="qris_details" class="bank-details">
            <div style="text-align: center; margin: 20px 0;">
                        <img src="images/qris-placeholder.png" alt="QRIS Code" style="max-width: 250px;">
                        <p style="margin-top: 10px;"><strong>Jumlah:</strong> <span id="qris_amount">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span></p>
                    </div>
                <div class="form-group">
                    <label for="transaction_id">ID Transaksi QRIS</label>
                    <input type="text" name="transaction_id" id="transaction_id" placeholder="Masukkan ID transaksi" required>
                </div>
<!--                 
                <div class="form-group">
                    <label for="payment_proof">Unggah Bukti Transfer</label>
                    <input type="file" name="payment_proof" id="payment_proof" accept="image/jpeg,image/png,image/jpg,application/pdf">
                </div> -->
                
                <div class="form-group">
                    <label for="notes">Catatan (Opsional)</label>
                    <textarea name="notes" id="notes" rows="3"></textarea>
                </div>
            </div>
            
            <div class="button-group">
                <a href="riwayat_pesanan.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="submit-button">Konfirmasi Pembayaran</button>
            </div>
        </form>
    </div>

    <script>
        // Handle payment method selection
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');
                
                const method = this.dataset.method;
                document.querySelector(`input[value="${method}"]`).checked = true;
                
                document.getElementById('bank_transfer_details').style.display = 'none';
                document.getElementById('qris_details').style.display = 'none';
                
                if (method === 'transfer') {
                    document.getElementById('bank_transfer_details').style.display = 'block';
                } else if (method === 'qris') {
                    document.getElementById('qris_details').style.display = 'block';
                }
            });
        });

        // Validasi sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            
            if (!paymentMethod) {
                e.preventDefault();
                alert('Silakan pilih metode pembayaran terlebih dahulu');
                return;
            }
            
            if (paymentMethod.value === 'transfer') {
                const paymentProof = document.getElementById('payment_proof');
                if (!paymentProof.files.length) {
                    e.preventDefault();
                    alert('Silakan unggah bukti transfer');
                    return;
                }
            } else {
                const transactionId = document.getElementById('transaction_id');
                if (!transactionId.value.trim()) {
                    e.preventDefault();
                    alert('Silakan masukkan ID Transaksi');
                    return;
                }
            }
        });
    </script>
</body>
</html>