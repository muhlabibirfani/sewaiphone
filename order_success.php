<?php
include 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

$order_query = "SELECT o.produk_id, p.nama_produk as produk_nama, p.harga_sewa, u.name as pelanggan 
               FROM orders o
               JOIN produk p ON o.produk_id = p.id
               JOIN users u ON o.user_id = u.id
               WHERE o.id = $order_id AND (o.user_id = $user_id OR {$user_id} = (SELECT id FROM users WHERE role = 'admin' LIMIT 1))";
$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header("Location: index.php");
    exit();
}

// Calculate days
$sewa = new DateTime($order['tanggal_sewa']);
$kembali = new DateTime($order['tanggal_kembali']);
$interval = $sewa->diff($kembali);
$days = $interval->days + 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Berhasil | Fanzzervice</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
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
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin: 20px 0;
        }
        .order-details {
            text-align: left;
            margin: 30px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .order-details h2 {
            margin-top: 0;
            color: #0071e3;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: 500;
            width: 150px;
        }
        .btn {
            display: inline-block;
            background-color: #0071e3;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #005bb5;
        }
        .btn-secondary {
            background-color: #6c757d;
            margin-left: 10px;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Pesanan Anda Berhasil!</h1>
        <p>Terima kasih telah memilih Fanzzervice. Berikut detail pesanan Anda:</p>
        
        <div class="order-details">
            <h2>Detail Pesanan</h2>
            
            <div class="detail-row">
                <div class="detail-label">Nomor Pesanan</div>
                <div>: #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Produk</div>
                <div>: <?php echo $order['produk_nama']; ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Harga Sewa/hari</div>
                <div>: Rp <?php echo number_format($order['harga_sewa'], 0, ',', '.'); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Tanggal Sewa</div>
                <div>: <?php echo date('d M Y', strtotime($order['tanggal_sewa'])); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Tanggal Kembali</div>
                <div>: <?php echo date('d M Y', strtotime($order['tanggal_kembali'])); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Durasi</div>
                <div>: <?php echo $days; ?> hari</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Total Harga</div>
                <div>: Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Status</div>
                <div>: <?php echo ucfirst($order['status']); ?></div>
            </div>
        </div>
        
        <p>Kami akan segera menghubungi Anda untuk konfirmasi lebih lanjut.</p>
        <p>Untuk pertanyaan, hubungi WhatsApp: <strong>0812-3456-7890</strong></p>
        
        <div>
            <a href="index.php" class="btn">Kembali ke Beranda</a>
            <?php if($_SESSION['user_role'] == 'admin'): ?>
                <a href="return.php" class="btn btn-secondary">Ke Halaman Pengembalian</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>