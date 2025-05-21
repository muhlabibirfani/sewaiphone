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

// Get payment details for this order (most recent)
$payment_query = "SELECT * FROM payments WHERE order_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $payment_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$payment_result = mysqli_stmt_get_result($stmt);
$payment = mysqli_fetch_assoc($payment_result);

// Format payment method name for display
$payment_method_names = [
    'transfer' => 'Transfer Bank',
    'credit_card' => 'Kartu Kredit/Debit',
    'qris' => 'QRIS'
];

$payment_method_display = $payment_method_names[$payment['method']] ?? $payment['method'];

// Format payment status for display
$payment_status_names = [
    'pending' => 'Menunggu Konfirmasi',
    'success' => 'Berhasil',
    'failed' => 'Gagal'
];

$payment_status_display = $payment_status_names[$payment['status']] ?? $payment['status'];

// Format payment type for display
$payment_type_names = [
    'full' => 'Pembayaran Penuh',
    'dp' => 'Uang Muka (DP)'
];

$payment_type_display = $payment_type_names[$payment['payment_type']] ?? $payment['payment_type'];

// Calculate rental duration
$start_date = new DateTime($order['tanggal_sewa']);
$end_date = new DateTime($order['tanggal_kembali']);
$interval = $start_date->diff($end_date);
$days = $interval->days + 1; // Include both start and end dates
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil | Fanzzervice</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .success-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .success-icon {
            color: #4CAF50;
            font-size: 80px;
            margin-bottom: 20px;
        }
        h1 {
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
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
            flex-shrink: 0;
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
        .payment-info {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .payment-info h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
        }
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .payment-row:last-child {
            border-bottom: none;
        }
        .payment-label {
            color: #666;
            font-weight: 500;
        }
        .payment-value {
            font-weight: 600;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
        }
        .status-pending {
            background-color: #FFF3CD;
            color: #856404;
        }
        .status-success {
            background-color: #D4EDDA;
            color: #155724;
        }
        .status-failed {
            background-color: #F8D7DA;
            color: #721C24;
        }
        .next-steps {
            background-color: #e8f4ff;
            border-left: 4px solid #0071e3;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .next-steps h2 {
            margin-top: 0;
            color: #0071e3;
        }
        .next-steps ol {
            margin-bottom: 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin-bottom: 10px;
        }
        .next-steps li:last-child {
            margin-bottom: 0;
        }
        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn {
            flex: 1;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background-color: #0071e3;
            color: white;
            border: none;
        }
        .btn-primary:hover {
            background-color: #005bb5;
        }
        .btn-outline {
            background-color: white;
            color: #0071e3;
            border: 2px solid #0071e3;
        }
        .btn-outline:hover {
            background-color: #f0f7ff;
        }
        @media (max-width: 768px) {
            .buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Terima Kasih!</h1>
            <p class="subtitle">Pembayaran Anda telah berhasil diproses</p>
        </div>
        
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
                        <p><strong>Durasi:</strong> <?php echo $days; ?> hari</p>
                    </div>
                    <div class="price-details">
                        <p>Harga sewa: Rp <?php echo number_format($order['harga_sewa'], 0, ',', '.'); ?> x <?php echo $days; ?> hari</p>
                        <p class="total">Total: Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="payment-info">
            <h2>Informasi Pembayaran</h2>
            
            <div class="payment-row">
                <div class="payment-label">ID Pembayaran</div>
                <div class="payment-value">#<?php echo $payment['id']; ?></div>
            </div>
            
            <div class="payment-row">
                <div class="payment-label">Tanggal Pembayaran</div>
                <div class="payment-value"><?php echo date('d F Y H:i', strtotime($payment['payment_date'])); ?></div>
            </div>
            
            <div class="payment-row">
                <div class="payment-label">Jenis Pembayaran</div>
                <div class="payment-value"><?php echo $payment_type_display; ?></div>
            </div>
            
            <div class="payment-row">
                <div class="payment-label">Metode Pembayaran</div>
                <div class="payment-value"><?php echo $payment_method_display; ?></div>
            </div>
            
            <div class="payment-row">
                <div class="payment-label">Jumlah Dibayarkan</div>
                <div class="payment-value">Rp <?php echo number_format($payment['amount'], 0, ',', '.'); ?></div>
            </div>
            
            <?php if (!empty($payment['transaction_id'])): ?>
            <div class="payment-row">
                <div class="payment-label">ID Transaksi</div>
                <div class="payment-value"><?php echo $payment['transaction_id']; ?></div>
            </div>
            <?php endif; ?>
            
            <div class="payment-row">
                <div class="payment-label">Status</div>
                <div class="payment-value">
                    <span class="status status-<?php echo $payment['status']; ?>">
                        <?php echo $payment_status_display; ?>
                    </span>
                </div>
            </div>
            
            <?php if ($payment['payment_type'] == 'dp'): ?>
            <div class="payment-row">
                <div class="payment-label">Sisa Pembayaran</div>
                <div class="payment-value">Rp <?php echo number_format($order['total_harga'] - $payment['amount'], 0, ',', '.'); ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="next-steps">
            <h2>Langkah Selanjutnya</h2>
            
            <?php if ($payment['method'] == 'transfer' && $payment['status'] == 'pending'): ?>
                <p>Pembayaran transfer bank Anda sedang menunggu konfirmasi. Langkah selanjutnya:</p>
                <ol>
                    <li>Tim kami akan memproses pembayaran Anda dalam 1x24 jam kerja</li>
                    <li>Anda akan menerima notifikasi email ketika pembayaran dikonfirmasi</li>
                    <li>Silakan cek status pesanan di halaman "Pesanan Saya"</li>
                </ol>
            <?php elseif ($payment['payment_type'] == 'dp'): ?>
                <p>Uang muka (DP) Anda telah diterima. Langkah selanjutnya:</p>
                <ol>
                    <li>Datang ke toko kami pada tanggal <?php echo date('d F Y', strtotime($order['tanggal_sewa'])); ?></li>
                    <li>Bawa identitas diri (KTP/SIM/Paspor)</li>
                    <li>Selesaikan sisa pembayaran sebesar Rp <?php echo number_format($order['total_harga'] - $payment['amount'], 0, ',', '.'); ?></li>
                    <li>Tanda tangani kontrak sewa</li>
                </ol>
            <?php else: ?>
                <p>Pembayaran Anda telah dikonfirmasi. Langkah selanjutnya:</p>
                <ol>
                    <li>Datang ke toko kami pada tanggal <?php echo date('d F Y', strtotime($order['tanggal_sewa'])); ?></li>
                    <li>Bawa identitas diri (KTP/SIM/Paspor)</li>
                    <li>Tanda tangani kontrak sewa</li>
                </ol>
            <?php endif; ?>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="buttons">
            <a href="order_detail.php?id=<?php echo $order_id; ?>" class="btn btn-outline">Lihat Detail Pesanan</a>
            <a href="my_orders.php" class="btn btn-primary">Pesanan Saya</a>
        </div>
    </div>
</body>
</html>