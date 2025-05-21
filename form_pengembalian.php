<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['order_id']) && !empty($_POST['order_id'])) {
        $order_id = $_POST['order_id'];
        $reason = $_POST['reason'];
        $description = $_POST['description'];
        $user_id = $_SESSION['user_id'];
        
        // Validate order exists and belongs to user
        $stmt = $conn->prepare("SELECT o.id, p.nama_produk, o.status 
                                FROM orders o 
                                JOIN produk p ON o.produk_id = p.id 
                                WHERE o.id = ? AND o.user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $error = "Order ID tidak valid atau tidak ditemukan.";
        } else {
            $order = $result->fetch_assoc();
            
            // Check if return request already exists
            $check_stmt = $conn->prepare("SELECT id FROM returns WHERE order_id = ?");
            $check_stmt->bind_param("i", $order_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "Anda sudah mengajukan pengembalian untuk pesanan ini sebelumnya.";
            } else {
                // Mulai transaksi database
                $conn->begin_transaction();
                
                try {
                    // Insert return request
                    $stmt = $conn->prepare("INSERT INTO returns (order_id, user_id, reason, description, status, created_at) 
                                          VALUES (?, ?, ?, ?, 'pending', NOW())");
                    $stmt->bind_param("iiss", $order_id, $user_id, $reason, $description);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Gagal menyimpan pengajuan pengembalian");
                    }
                    
                    // Update order status to "selesai"
                    $update_stmt = $conn->prepare("UPDATE orders SET status = 'selesai' WHERE id = ?");
                    $update_stmt->bind_param("i", $order_id);
                    
                    if (!$update_stmt->execute()) {
                        throw new Exception("Gagal mengupdate status pesanan");
                    }
                    
                    // Log the return
                    $return_id = $stmt->insert_id;
                    $log_stmt = $conn->prepare("INSERT INTO return_logs (return_id, user_id, action, notes) 
                                              VALUES (?, ?, 'Pengajuan pengembalian', 'Pengembalian diajukan oleh pelanggan')");
                    $log_stmt->bind_param("ii", $return_id, $user_id);
                    
                    if (!$log_stmt->execute()) {
                        throw new Exception("Gagal mencatat log pengembalian");
                    }
                    
                    // Commit transaksi jika semua berhasil
                    $conn->commit();
                    
                    $success = "Permintaan pengembalian berhasil diajukan. Status pesanan telah diubah menjadi Selesai. Kami akan menghubungi Anda dalam 1-2 hari kerja.";
                } catch (Exception $e) {
                    // Rollback jika ada error
                    $conn->rollback();
                    $error = "Terjadi kesalahan: " . $e->getMessage();
                }
            }
        }
    } else {
        $error = "Harap pilih pesanan yang akan dikembalikan.";
    }
}

// Get user's orders for dropdown - MODIFIED QUERY TO EXCLUDE RETURNED ORDERS
$orders = [];
$stmt = $conn->prepare("SELECT o.id, p.nama_produk, o.tanggal_sewa, o.tanggal_kembali, o.status 
                        FROM orders o 
                        JOIN produk p ON o.produk_id = p.id 
                        LEFT JOIN returns r ON o.id = r.order_id
                        WHERE o.user_id = ? AND r.id IS NULL");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengembalian - Fanzzervice</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f7;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #0071e3;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        select, textarea, input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        select:focus, textarea:focus, input:focus {
            border-color: #0071e3;
            outline: none;
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-submit {
            background-color: #0071e3;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
            display: block;
            width: 100%;
        }
        
        .btn-submit:hover {
            background-color: #005bb5;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        
        .info-box {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #0071e3;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-exchange-alt"></i> Form Pengembalian Produk</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <h3><i class="fas fa-info-circle"></i> Informasi Penting</h3>
            <p>Silakan isi form berikut untuk mengajukan pengembalian produk. Pastikan produk dalam kondisi baik dan belum digunakan secara berlebihan. Tim kami akan menghubungi Anda dalam 1-2 hari kerja.</p>
        </div>
        
        <form action="form_pengembalian.php" method="POST">
            <div class="form-group">
                <label for="order_id">Pesanan yang akan dikembalikan</label>
                <select id="order_id" name="order_id" required>
                    <option value="">-- Pilih Pesanan --</option>
                    <?php foreach ($orders as $order): ?>
                        <option value="<?php echo $order['id']; ?>">
                            #<?php echo $order['id']; ?> - <?php echo $order['nama_produk']; ?> - 
                            Tgl Sewa: <?php echo date('d/m/Y', strtotime($order['tanggal_sewa'])); ?> - 
                            Status: <?php echo ucfirst($order['status']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="reason">Alasan Pengembalian</label>
                <select id="reason" name="reason" required>
                    <option value="">-- Pilih Alasan --</option>
                    <option value="Produk cacat">Produk cacat</option>
                    <option value="Tidak sesuai deskripsi">Tidak sesuai deskripsi</option>
                    <option value="Pesanan salah">Pesanan salah</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi Lengkap</label>
                <textarea id="description" name="description" required placeholder="Jelaskan secara detail alasan pengembalian dan kondisi produk"></textarea>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Ajukan Pengembalian
            </button>
        </form>
    </div>
</body>
</html>