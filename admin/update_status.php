<?php
include '../config.php';

// Validate and sanitize inputs - Fixed filter_input parameters (removed quotes)
$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$status = strtolower(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
$shipping_code = filter_input(INPUT_POST, 'shipping_code', FILTER_SANITIZE_STRING);

// Validate required fields
if (!$order_id || !$status) {
    $_SESSION['error'] = "Data input tidak valid";
    header("Location: order.php");
    exit;
}

// Validate status against allowed values
$allowed_statuses = ['pending', 'menunggupembayaran', 'dikirim', 'selesai', 'dibatalkan', 'dipinjam'];
if (!in_array($status, $allowed_statuses)) {
    $_SESSION['error'] = "Status pesanan tidak valid";
    header("Location: order.php?id=" . $order_id);
    exit;
}

// Validate shipping code if status is 'dikirim'
if ($status === 'dikirim') {
    if (empty($shipping_code)) {
        $_SESSION['error'] = "Kode pengiriman wajib diisi untuk status 'Dikirim'";
        header("Location: order.php?id=" . $order_id);
        exit;
    }
    
    // Additional shipping code validation
    if (!preg_match('/^[A-Za-z0-9\-]{5,20}$/', $shipping_code)) {
        $_SESSION['error'] = "Kode pengiriman harus 5-20 karakter alfanumerik";
        header("Location: order.php?id=" . $order_id);
        exit;
    }
}

// Prepare the update query using prepared statements
$query = "UPDATE orders SET 
          status = ?, 
          shipping_code = ?,
          updated_at = NOW()
          WHERE id = ?";

$stmt = mysqli_prepare($conn, $query);
if ($stmt === false) {
    $_SESSION['error'] = "Error database: " . mysqli_error($conn);
    header("Location: order.php");
    exit;
}

// Bind parameters
if ($status === 'dikirim') {
    mysqli_stmt_bind_param($stmt, "ssi", $status, $shipping_code, $order_id);
} else {
    $shipping_code = null; // Set to NULL for non-shipping statuses
    mysqli_stmt_bind_param($stmt, "ssi", $status, $shipping_code, $order_id);
}

// Execute the statement
if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = "Status pesanan berhasil diperbarui!";
    
    // Log this action
    $user_id = $_SESSION['user_id'];
    $log_message = "Admin $user_id memperbarui pesanan $order_id ke status $status";
    error_log($log_message);
    
    // Optional: Send notification to customer
    // sendStatusUpdateNotification($order_id, $status);
} else {
    $_SESSION['error'] = "Gagal memperbarui status pesanan: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);

// Redirect back to the order page
header("Location: order.php?id=" . $order_id);
exit;
?>