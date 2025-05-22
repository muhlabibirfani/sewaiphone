<?php
include '../config.php';

// Check if admin is logged in
// session_start();
// if(!isset($_SESSION['admin_id'])) {
//     header("Location: login.php");
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize inputs - Improved validation
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $new_status = strtolower(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
    $shipping_code = filter_input(INPUT_POST, 'shipping_code', FILTER_SANITIZE_STRING);
    
    // Validate required fields
    if (!$order_id || !$new_status) {
        $_SESSION['error'] = "Data input tidak valid";
        header("Location: order.php");
        exit();
    }
    
    // Validate status against allowed values
    $allowed_statuses = ['pending', 'menunggupembayaran', 'menunggudikirim', 'dikirim', 'dipinjam', 'selesai', 'dibatalkan'];
    if (!in_array($new_status, $allowed_statuses)) {
        $_SESSION['error'] = "Status pesanan tidak valid";
        header("Location: order.php?id=" . $order_id);
        exit();
    }
    
    // Validate shipping code if status is 'dikirim'
    if ($new_status === 'dikirim') {
        if (empty($shipping_code)) {
            $_SESSION['error'] = "Kode pengiriman wajib diisi untuk status 'Dikirim'";
            header("Location: order.php?id=" . $order_id);
            exit();
        }
        
        // Additional shipping code validation
        if (!preg_match('/^[A-Za-z0-9\-]{5,20}$/', $shipping_code)) {
            $_SESSION['error'] = "Kode pengiriman harus 5-20 karakter alfanumerik";
            header("Location: order.php?id=" . $order_id);
            exit();
        }
    }
    
    // Start transaction for atomic operations
    mysqli_begin_transaction($conn);
    
    try {
        // Get current order details
        $order_query = "SELECT o.*, p.stok_tersedia, p.nama_produk 
                       FROM orders o 
                       JOIN produk p ON o.produk_id = p.id 
                       WHERE o.id = ?";
        $stmt = mysqli_prepare($conn, $order_query);
        
        if ($stmt === false) {
            throw new Exception("Error preparing query: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);
        
        if (!$order) {
            throw new Exception("Pesanan tidak ditemukan");
        }
        
        $old_status = $order['status'];
        $product_id = $order['produk_id'];
        $current_stock = $order['stok_tersedia'];
        
        // Handle stock changes based on status transitions
        $stock_change = 0;
        $stock_update_needed = false;
        
        // KUNCI UTAMA: Jika berubah dari 'pending' ke 'menunggupembayaran' - kurangi stok
        if ($old_status == 'pending' && $new_status == 'menunggupembayaran') {
            if ($current_stock <= 0) {
                throw new Exception("Stok tidak tersedia untuk produk: " . $order['nama_produk']);
            }
            $stock_change = -1; // Kurangi stok sebanyak 1
            $stock_update_needed = true;
        }
        // Jika berubah dari status yang sudah mengurangi stok kembali ke 'pending' atau 'dibatalkan' - kembalikan stok
        elseif (in_array($old_status, ['menunggupembayaran', 'menunggudikirim', 'dikirim', 'dipinjam']) && 
                in_array($new_status, ['pending', 'dibatalkan'])) {
            $stock_change = 1; // Kembalikan stok sebanyak 1
            $stock_update_needed = true;
        }
        // Jika berubah dari 'dipinjam' ke 'selesai' - kembalikan stok (barang sudah dikembalikan)
        elseif ($old_status == 'dipinjam' && $new_status == 'selesai') {
            $stock_change = 1; // Kembalikan stok sebanyak 1
            $stock_update_needed = true;
        }
        
        // Update stock jika diperlukan
        if ($stock_update_needed) {
            $new_stock = $current_stock + $stock_change;
            if ($new_stock < 0) {
                throw new Exception("Stok tidak mencukupi");
            }
            
            $update_stock_query = "UPDATE produk SET stok_tersedia = ? WHERE id = ?";
            $stock_stmt = mysqli_prepare($conn, $update_stock_query);
            
            if ($stock_stmt === false) {
                throw new Exception("Error preparing stock update query: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stock_stmt, "ii", $new_stock, $product_id);
            
            if (!mysqli_stmt_execute($stock_stmt)) {
                throw new Exception("Gagal memperbarui stok: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stock_stmt);
        }
        
        // Update order status
        $update_query = "UPDATE orders SET status = ?, updated_at = NOW()";
        $params = [$new_status];
        $param_types = "s";
        
        // Tambahkan shipping code jika status 'dikirim' dan kode pengiriman disediakan
        if ($new_status == 'dikirim' && !empty($shipping_code)) {
            $update_query .= ", shipping_code = ?";
            $params[] = $shipping_code;
            $param_types .= "s";
        } elseif ($new_status != 'dikirim') {
            // Hapus shipping code jika status bukan 'dikirim'
            $update_query .= ", shipping_code = NULL";
        }
        
        $update_query .= " WHERE id = ?";
        $params[] = $order_id;
        $param_types .= "i";
        
        $update_stmt = mysqli_prepare($conn, $update_query);
        
        if ($update_stmt === false) {
            throw new Exception("Error preparing order update query: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($update_stmt, $param_types, ...$params);
        
        if (!mysqli_stmt_execute($update_stmt)) {
            throw new Exception("Gagal memperbarui status pesanan: " . mysqli_error($conn));
        }
        
        mysqli_stmt_close($update_stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Success message
        $status_messages = [
            'pending' => 'Pending',
            'menunggupembayaran' => 'Menunggu Pembayaran',
            'menunggudikirim' => 'Menunggu Dikirim',
            'dikirim' => 'Dikirim',
            'dipinjam' => 'Dipinjam',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan'
        ];
        
        $message = "Status pesanan #$order_id berhasil diubah menjadi: " . $status_messages[$new_status];
        
        if ($stock_update_needed) {
            if ($stock_change > 0) {
                $message .= " (Stok dikembalikan: +" . $stock_change . ")";
            } else {
                $message .= " (Stok dikurangi: " . $stock_change . ")";
            }
        }
        
        $_SESSION['success'] = $message;
        
        // Log this action for audit trail
        if (isset($_SESSION['admin_id'])) {
            $admin_id = $_SESSION['admin_id'];
            $log_message = "Admin $admin_id memperbarui pesanan $order_id ke status $new_status";
            error_log($log_message);
        }
        
        // Optional: Send notification to customer
        // sendStatusUpdateNotification($order_id, $new_status);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        
        // Log the error
        error_log("Order update error: " . $e->getMessage() . " for order ID: $order_id");
    }
} else {
    $_SESSION['error'] = "Metode tidak diizinkan";
}

// Redirect back to orders page
if (isset($order_id) && $order_id > 0) {
    header("Location: order.php?id=" . $order_id);
} else {
    header("Location: order.php");
}
exit();
?>