<?php
// Sesuaikan dengan file koneksi Anda
include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
// Mengubah query untuk menggunakan tabel orders dengan semua kolom yang diperlukan

$query = "SELECT o.id, o.produk_id, p.nama_produk as nama, o.status, o.created_at, 
                 o.tanggal_sewa, o.tanggal_kembali, o.total_harga 
          FROM orders o 
          JOIN produk p ON o.produk_id = p.id 
          WHERE o.user_id = ? 
          ORDER BY o.created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Proses konfirmasi pesanan diterima (sudah sampai)
if (isset($_POST['konfirmasi_terima']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    // Verifikasi bahwa pesanan ini milik user yang sedang login
    $check_query = "SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'dikirim'";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ii", $order_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update status menjadi dipinjam
        $update_query = "UPDATE orders SET status = 'dipinjam', updated_at = CURRENT_TIMESTAMP() WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "i", $order_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            // Redirect untuk refresh halaman
            header("Location: riwayat_pesanan.php?status=received");
            exit();
        } else {
            $error_message = "Terjadi kesalahan saat memperbarui status pesanan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Fanzzervice</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f7;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #0071e3;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .pesanan-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .pesanan-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .pesanan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 113, 227, 0.1);
        }
        
        .pesanan-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .pesanan-id {
            font-weight: bold;
            color: #0071e3;
        }
        
        .pesanan-tanggal {
            color: #666;
            font-size: 0.9em;
        }
        
        .pesanan-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .status-selesai {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-batal {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-pending {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .status-menunggupembayaran {
            background-color:rgb(249, 233, 186);
            color: #856404;
        }

        .status-menunggudikirim {
            background-color:rgb(215, 245, 186);
            color:rgb(90, 179, 7);
        }
        
        .status-dikirim {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-dipinjam {
            background-color: rgb(209, 250, 255);
            color: rgb(10, 182, 204);
        }

        .pesanan-detail {
            margin-top: 10px;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .detail-label {
            font-weight: bold;
        }
        
        .empty-message {
            text-align: center;
            padding: 40px;
            color: #666;
            grid-column: 1 / -1;
        }
        
        .btn-bayar, .btn-terima {
            display: inline-block;
            margin-left: 10px;
            padding: 3px 10px;
            background-color: #0071e3;
            color: white;
            border-radius: 15px;
            text-decoration: none;
            font-size: 0.8em;
            font-weight: bold;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-bayar:hover, .btn-terima:hover {
            background-color: #005bbf;
        }

        .btn-terima {
            background-color: #28a745;
        }
        
        .btn-terima:hover {
            background-color: #218838;
        }

        .status-alert {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .status-alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        @media (max-width: 768px) {
            .pesanan-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-history"></i> Riwayat Pesanan</h1>
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'received'): ?>
        <div class="status-alert success">
            <i class="fas fa-check-circle"></i> Pesanan berhasil dikonfirmasi diterima. Status telah diubah menjadi "DIPINJAM".
        </div>
        <?php endif; ?>
        
        <div class="pesanan-list">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($pesanan = mysqli_fetch_assoc($result)): ?>
                    <div class="pesanan-card">
                        <div class="pesanan-header">
                            <span class="pesanan-id">#<?= htmlspecialchars($pesanan['id']) ?></span>
                            <span class="pesanan-tanggal">
                                <?= !empty($pesanan['created_at']) ? date('d M Y H:i', strtotime($pesanan['created_at'])) : 'Tanggal tidak tersedia' ?>
                            </span>
                        </div>
                        
                        <div>
                            <?php
                            $statusClass = "status-pending";
                            $statusText = "PENDING";
                            $showPaymentButton = false;
                            $showReceiveButton = false;
                            
                            if (!empty($pesanan['status'])) {
                                switch(strtolower($pesanan['status'])) {
                                    case 'pending':
                                        $statusClass = "status-pending";
                                        $statusText = "PENDING";
                                        break;
                                    case 'menunggupembayaran':
                                        $statusClass = "status-menunggupembayaran";
                                        $statusText = "MENUNGGU PEMBAYARAN";
                                        $showPaymentButton = true;
                                        break;
                                    case 'menunggudikirim':
                                        $statusClass = "status-menunggudikirim";
                                        $statusText = "MENUNGGU DIKIRIM";
                                        break;
                                    case 'dikirim':
                                        $statusClass = "status-dikirim";
                                        $statusText = "DIKIRIM";
                                        $showReceiveButton = true;
                                        break;
                                    case 'dipinjam':
                                        $statusClass = "status-dipinjam";
                                        $statusText = "DIPINJAM";
                                        break;
                                    case 'selesai':
                                        $statusClass = "status-selesai";
                                        $statusText = "SELESAI";
                                        break;
                                    case 'batal':
                                        $statusClass = "status-batal";
                                        $statusText = "DIBATALKAN";
                                        break;
                                    default:
                                }
                            }
                            ?>
                            <span class="pesanan-status <?= $statusClass ?>">
                                <?= $statusText ?>
                            </span>
                            <?php if ($showPaymentButton): ?>
                                <a href="payment.php?order_id=<?= $pesanan['id'] ?>" class="btn-bayar">
                                    <i class="fas fa-credit-card"></i> Bayar Sekarang
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($showReceiveButton): ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?= $pesanan['id'] ?>">
                                    <button type="submit" name="konfirmasi_terima" class="btn-terima">
                                        <i class="fas fa-check-circle"></i> Sudah Sampai
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        
                        <div class="pesanan-detail">
                            <div class="detail-item">
                                <span class="detail-label">Produk:</span>
                                <span><?= htmlspecialchars($pesanan['nama']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Tanggal Sewa:</span>
                                <span>
                                    <?= !empty($pesanan['tanggal_sewa']) ? date('d M Y', strtotime($pesanan['tanggal_sewa'])) : 'Tidak tersedia' ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Tanggal Kembali:</span>
                                <span>
                                    <?= !empty($pesanan['tanggal_kembali']) ? date('d M Y', strtotime($pesanan['tanggal_kembali'])) : 'Tidak tersedia' ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Total:</span>
                                <span>Rp <?= isset($pesanan['total_harga']) ? number_format($pesanan['total_harga'], 0, ',', '.') : '0' ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-message">
                    <i class="fas fa-box-open fa-3x" style="color: #ccc; margin-bottom: 15px;"></i>
                    <h3>Belum ada riwayat pesanan</h3>
                    <p>Anda belum melakukan pemesanan apapun.</p>
                    <a href="index.php" class="btn-masuk" style="display: inline-block; margin-top: 15px;">
                        <i class="fas fa-shopping-cart"></i> Belanja Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>