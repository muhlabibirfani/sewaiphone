<?php
require_once '../config.php';

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil ID pesanan dari parameter URL dengan validasi
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    die("ID pesanan tidak valid.");
}

// Ambil detail pesanan dengan error handling
try {
    $stmt = $conn->prepare("
        SELECT o.*, u.name as nama_user, u.email, u.phone, u.address, 
               p.nama_produk, p.harga_sewa, p.gambar 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN produk p ON o.produk_id = p.id
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Pesanan tidak ditemukan.");
    }

    $order = $result->fetch_assoc();
    
    // Validasi data order yang penting
    if (!isset($order['status'])) {
        $order['status'] = 'pending';
    }
    
    // Set default values untuk field yang mungkin null
    $order['tanggal_sewa'] = $order['tanggal_sewa'] ?? date('Y-m-d');
    $order['tanggal_kembali'] = $order['tanggal_kembali'] ?? date('Y-m-d');
    $order['total_harga'] = $order['total_harga'] ?? 0;
    $order['metode_pembayaran'] = $order['metode_pembayaran'] ?? 'Belum ditentukan';

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Ambil histori pesanan
try {
    $stmt = $conn->prepare("
        SELECT * FROM order_logs 
        WHERE order_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $logs_result = $stmt->get_result();
    $logs = [];
    while ($log = $logs_result->fetch_assoc()) {
        $logs[] = $log;
    }
} catch (Exception $e) {
    $logs = [];
}

// Cek apakah ada pengembalian untuk pesanan ini
try {
    $stmt = $conn->prepare("
        SELECT r.*, rl.action, rl.notes, rl.created_at as log_date 
        FROM returns r
        LEFT JOIN return_logs rl ON r.id = rl.return_id
        WHERE r.order_id = ?
        ORDER BY rl.created_at DESC
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $returns_result = $stmt->get_result();
    $returns = [];
    while ($return = $returns_result->fetch_assoc()) {
        $returns[] = $return;
    }
} catch (Exception $e) {
    $returns = [];
}

// Update status pesanan jika form dikirim
$status_updated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'] ?? 'pending';
    $notes = $_POST['notes'] ?? '';
    
    try {
        // Update status pesanan
        $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $order_id);
        
        if ($update_stmt->execute()) {
            // Tambahkan log
            $admin_id = $_SESSION['admin_id'] ?? 0;
            $log_stmt = $conn->prepare("
                INSERT INTO order_logs (order_id, user_id, action, notes, status_from, status_to) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $action = "Update status: " . $new_status;
            $log_stmt->bind_param(
                "iissss", 
                $order_id, 
                $admin_id, 
                $action, 
                $notes,
                $order['status'],
                $new_status
            );
            $log_stmt->execute();
            
            $status_updated = true;
            // Update status di array order
            $order['status'] = $new_status;
            
            // Refresh logs
            $stmt = $conn->prepare("
                SELECT * FROM order_logs 
                WHERE order_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $logs_result = $stmt->get_result();
            $logs = [];
            while ($log = $logs_result->fetch_assoc()) {
                $logs[] = $log;
            }
        }
    } catch (Exception $e) {
        $error = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// Handle pengembalian
$return_processed = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_return'])) {
    $return_id = $_POST['return_id'] ?? 0;
    $return_status = $_POST['return_status'] ?? 'pending';
    $return_notes = $_POST['return_notes'] ?? '';
    
    try {
        // Update status pengembalian
        $update_stmt = $conn->prepare("UPDATE returns SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $return_status, $return_id);
        
        if ($update_stmt->execute()) {
            // Tambahkan log
            $admin_id = $_SESSION['admin_id'] ?? 0;
            $log_stmt = $conn->prepare("
                INSERT INTO return_logs (return_id, user_id, action, notes) 
                VALUES (?, ?, ?, ?)
            ");
            $action = "Update status pengembalian: " . $return_status;
            $log_stmt->bind_param("iiss", $return_id, $admin_id, $action, $return_notes);
            $log_stmt->execute();
            
            $return_processed = true;
            
            // Refresh data pengembalian
            $stmt = $conn->prepare("
                SELECT r.*, rl.action, rl.notes, rl.created_at as log_date 
                FROM returns r
                LEFT JOIN return_logs rl ON r.id = rl.return_id
                WHERE r.order_id = ?
                ORDER BY rl.created_at DESC
            ");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $returns_result = $stmt->get_result();
            $returns = [];
            while ($return = $returns_result->fetch_assoc()) {
                $returns[] = $return;
            }
        }
    } catch (Exception $e) {
        $error = "Gagal memproses pengembalian: " . $e->getMessage();
    }
}

// Fungsi helper untuk warna status
function getStatusColor($status) {
    $status = strtolower($status ?? 'pending');
    switch ($status) {
        case 'pending':
            return 'text-yellow-600 bg-yellow-100';
        case 'dikonfirmasi':
            return 'text-blue-600 bg-blue-100';
        case 'dikirim':
            return 'text-indigo-600 bg-indigo-100';
        case 'selesai':
            return 'text-green-600 bg-green-100';
        case 'dibatalkan':
            return 'text-red-600 bg-red-100';
        default:
            return 'text-gray-600 bg-gray-100';
    }
}

function getReturnStatusColor($status) {
    $status = strtolower($status ?? 'pending');
    switch ($status) {
        case 'pending':
            return 'text-yellow-600 bg-yellow-100';
        case 'approved':
            return 'text-green-600 bg-green-100';
        case 'rejected':
            return 'text-red-600 bg-red-100';
        case 'processed':
            return 'text-blue-600 bg-blue-100';
        default:
            return 'text-gray-600 bg-gray-100';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo $order_id; ?> - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100">
    
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    Detail Pesanan #<?php echo $order_id; ?>
                </h1>
                <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo getStatusColor($order['status']); ?>">
                    <?php echo ucfirst($order['status']); ?>
                </span>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($status_updated): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>Status pesanan berhasil diperbarui.</p>
                </div>
            <?php endif; ?>
            
            <?php if ($return_processed): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>Status pengembalian berhasil diperbarui.</p>
                </div>
            <?php endif; ?>
            
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Informasi Pesanan -->
                <div class="w-full md:w-2/3">
                    <div class="border rounded-lg p-4 mb-6">
                        <h2 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">Informasi Pesanan</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600 text-sm">Produk</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['nama_produk'] ?? 'Tidak tersedia'); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Harga Sewa</p>
                                <p class="font-medium">Rp <?php echo number_format($order['harga_sewa'] ?? 0, 0, ',', '.'); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Tanggal Sewa</p>
                                <p class="font-medium"><?php echo date('d/m/Y', strtotime($order['tanggal_sewa'])); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Tanggal Kembali</p>
                                <p class="font-medium"><?php echo date('d/m/Y', strtotime($order['tanggal_kembali'])); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Durasi</p>
                                <p class="font-medium">
                                    <?php 
                                    try {
                                        $date1 = new DateTime($order['tanggal_sewa']);
                                        $date2 = new DateTime($order['tanggal_kembali']);
                                        $interval = $date1->diff($date2);
                                        echo $interval->days . ' hari';
                                    } catch (Exception $e) {
                                        echo '0 hari';
                                    }
                                    ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Total Biaya</p>
                                <p class="font-medium">Rp <?php echo number_format($order['total_harga'] ?? 0, 0, ',', '.'); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Tanggal Order</p>
                                <p class="font-medium"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Metode Pembayaran</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['metode_pembayaran']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border rounded-lg p-4 mb-6">
                        <h2 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">Informasi Pelanggan</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600 text-sm">Nama</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['nama_user'] ?? 'Tidak tersedia'); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Email</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['email'] ?? 'Tidak tersedia'); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Telepon</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['phone'] ?? 'Tidak tersedia'); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Alamat Pengiriman</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['address'] ?? 'Tidak tersedia'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200 mb-4">
                        <ul class="flex flex-wrap -mb-px">
                            <li class="mr-2">
                                <a href="#" class="tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 active" data-tab="history">
                                    <i class="fas fa-history mr-2"></i>Riwayat Status
                                </a>
                            </li>
                            <li class="mr-2">
                                <a href="#" class="tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" data-tab="returns">
                                    <i class="fas fa-exchange-alt mr-2"></i>Pengembalian
                                    <?php if (count($returns) > 0): ?>
                                        <span class="bg-red-500 text-white text-xs rounded-full px-2 py-1 ml-1">
                                            <?php echo count($returns); ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Tab Content -->
                    <div id="history" class="tab-content active">
                        <div class="border rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">Riwayat Status Pesanan</h2>
                            <?php if (count($logs) > 0): ?>
                                <div class="space-y-4">
                                    <?php foreach ($logs as $log): ?>
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 h-4 w-4 rounded-full bg-blue-500 mt-1"></div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium"><?php echo htmlspecialchars($log['action'] ?? 'Aksi tidak diketahui'); ?></p>
                                                <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($log['created_at'] ?? 'now')); ?></p>
                                                <?php if (!empty($log['notes'])): ?>
                                                    <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($log['notes']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-500">Belum ada riwayat status.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div id="returns" class="tab-content">
                        <div class="border rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">Riwayat Pengembalian</h2>
                            <?php if (count($returns) > 0): ?>
                                <?php $current_return = $returns[0]; // Ambil pengembalian terbaru ?>
                                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="font-medium">Pengembalian #<?php echo htmlspecialchars($current_return['id'] ?? ''); ?></h3>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo getReturnStatusColor($current_return['status'] ?? ''); ?>">
                                            <?php echo ucfirst($current_return['status'] ?? 'pending'); ?>
                                        </span>
                                    </div>
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Alasan:</span> <?php echo htmlspecialchars($current_return['reason'] ?? 'Tidak disebutkan'); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Deskripsi:</span> <?php echo htmlspecialchars($current_return['description'] ?? 'Tidak ada deskripsi'); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Tanggal Pengajuan:</span> 
                                            <?php echo date('d/m/Y H:i', strtotime($current_return['created_at'] ?? 'now')); ?>
                                        </p>
                                    </div>
                                    
                                    <?php if (($current_return['status'] ?? '') === 'pending'): ?>
                                        <form method="POST" class="bg-white p-4 rounded-lg border mt-4">
                                            <h4 class="font-medium mb-3">Proses Pengembalian</h4>
                                            <input type="hidden" name="return_id" value="<?php echo htmlspecialchars($current_return['id'] ?? ''); ?>">
                                            <div class="mb-3">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                    Status Pengembalian
                                                </label>
                                                <select name="return_status" class="w-full px-3 py-2 border rounded-md">
                                                    <option value="approved">Disetujui</option>
                                                    <option value="rejected">Ditolak</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                    Catatan
                                                </label>
                                                <textarea name="return_notes" class="w-full px-3 py-2 border rounded-md" rows="3"></textarea>
                                            </div>
                                            <button type="submit" name="process_return" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                                Simpan
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <div class="mt-4">
                                        <h4 class="font-medium mb-2">Riwayat Status</h4>
                                        <div class="space-y-3">
                                            <?php 
                                            $return_logs = array_filter($returns, function($r) use ($current_return) {
                                                return ($r['id'] ?? null) === ($current_return['id'] ?? null) && isset($r['action']);
                                            });
                                            if (count($return_logs) > 0):
                                            ?>
                                                <?php foreach ($return_logs as $rlog): ?>
                                                    <div class="flex items-start">
                                                        <div class="flex-shrink-0 h-3 w-3 rounded-full bg-blue-500 mt-1"></div>
                                                        <div class="ml-3">
                                                            <p class="text-sm font-medium"><?php echo htmlspecialchars($rlog['action'] ?? 'Aksi tidak diketahui'); ?></p>
                                                            <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($rlog['log_date'] ?? 'now')); ?></p>
                                                            <?php if (!empty($rlog['notes'])): ?>
                                                                <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($rlog['notes']); ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p class="text-sm text-gray-500">Belum ada riwayat status.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-500">Belum ada pengembalian untuk pesanan ini.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar - Update Status -->
                <div class="w-full md:w-1/3">
                    <div class="border rounded-lg p-4 sticky top-4">
                        <h2 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">Update Status</h2>
                        <form method="POST">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Status Pesanan
                                </label>
                                <select name="status" class="w-full px-3 py-2 border rounded-md">
                                    <option value="pending" <?php echo ($order['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="dikonfirmasi" <?php echo ($order['status'] === 'dikonfirmasi') ? 'selected' : ''; ?>>Dikonfirmasi</option>
                                    <option value="dikirim" <?php echo ($order['status'] === 'dikirim') ? 'selected' : ''; ?>>Dikirim</option>
                                    <option value="selesai" <?php echo ($order['status'] === 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                    <option value="dibatalkan" <?php echo ($order['status'] === 'dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Catatan
                                </label>
                                <textarea name="notes" class="w-full px-3 py-2 border rounded-md" rows="4" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                            </div>
                            <button type="submit" name="update_status" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Simpan Perubahan
                            </button>
                        </form>
                        <div class="mt-6">
                            <a href="order.php" class="block text-center w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Pesanan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab handling
        const tabLinks = document.querySelectorAll('.tab-link');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabLinks.forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                
                // Remove active class from all tabs
                tabLinks.forEach(el => el.classList.remove('active', 'text-blue-600', 'border-blue-600'));
                tabContents.forEach(el => el.classList.remove('active'));
                
                // Add active class to current tab
                link.classList.add('active', 'text-blue-600', 'border-blue-600');
                const tabId = link.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
    </script>
</body>
</html>