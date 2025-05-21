<?php
include 'config.php';

// Only admin should access this
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $order_id = $_GET['id'];
    
    // Get order details
    $order_query = "SELECT * FROM orders WHERE id = $order_id";
    $order_result = mysqli_query($conn, $order_query);
    $order = mysqli_fetch_assoc($order_result);
    
    if($order) {
        // Update order status
        $update_order = "UPDATE orders SET status = 'selesai' WHERE id = $order_id";
        mysqli_query($conn, $update_order);
        
        // Increase available stock
        $update_stock = "UPDATE produk SET stok_tersedia = stok_tersedia + 1 WHERE id = {$order['produk_id']}";
        mysqli_query($conn, $update_stock);
        
        $success = "Produk telah dikembalikan dan stok diperbarui";
    } else {
        $error = "Order tidak ditemukan";
    }
}

// Get all active rentals
$rentals_query = "SELECT o.produk_id, p.nama as produk_nama, u.name as pelanggan, 
                 o.tanggal_sewa, o.tanggal_kembali, o.status 
                 FROM orders o
                 JOIN produk p ON o.produk_id = p.id
                 JOIN users u ON o.user_id = u.id
                 WHERE o.status != 'selesai'
                 ORDER BY o.tanggal_kembali ASC";
$rentals_result = mysqli_query($conn, $rentals_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Produk | Fanzzervice</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .btn {
            padding: 8px 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #218838;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-pending {
            color: #ffc107;
        }
        .status-diproses {
            color: #17a2b8;
        }
        .status-dikirim {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-exchange-alt"></i> Manajemen Pengembalian</h1>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <h2>Daftar Penyewaan Aktif</h2>
        <table>
            <thead>
                <tr>
                    <th>ID Order</th>
                    <th>Produk</th>
                    <th>Pelanggan</th>
                    <th>Tanggal Sewa</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($rental = mysqli_fetch_assoc($rentals_result)): ?>
                <tr>
                    <td>#<?php echo str_pad($rental['id'], 6, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo $rental['produk_nama']; ?></td>
                    <td><?php echo $rental['pelanggan']; ?></td>
                    <td><?php echo date('d M Y', strtotime($rental['tanggal_sewa'])); ?></td>
                    <td><?php echo date('d M Y', strtotime($rental['tanggal_kembali'])); ?></td>
                    <td class="status-<?php echo $rental['status']; ?>"><?php echo ucfirst($rental['status']); ?></td>
                    <td>
                        <?php if($rental['status'] != 'selesai'): ?>
                            <a href="return.php?id=<?php echo $rental['id']; ?>" class="btn">
                                <i class="fas fa-check"></i> Tandai Dikembalikan
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>