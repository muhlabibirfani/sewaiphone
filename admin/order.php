<?php include '../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan | Fanzzervice</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --white: #ffffff;
            --gray: #6c757d;
            --transition: all 0.3s ease;
            --shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            z-index: 100;
            border-right: 1px solid #eee;
        }
        
        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
        }
        
        .sidebar-header i {
            color: #007bff;
            margin-right: 10px;
            font-size: 24px;
        }
        
        .sidebar-header h3 {
            font-size: 22px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 15px 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: 0.2s;
            font-size: 16px;
        }
        
        .sidebar-menu li a:hover {
            background-color: #f8f9fa;
        }
        
        .sidebar-menu li a.active {
            background-color: #e8f3ff;
            color: #007bff;
            border-left: 3px solid #007bff;
        }
        
        .sidebar-menu li a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-left: 5px;
            padding: 20px;
            transition: var(--transition);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .header h2 {
            color: var(--dark);
            font-size: 24px;
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .card-header h3 {
            font-size: 18px;
            color: var(--dark);
            font-weight: 600;
        }
        
        .form-control {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: var(--transition);
            min-width: 200px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .table-responsive {
            overflow-x: auto;
            padding: 0 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-menunggupembayaran {
            background-color: #ffeaa7;
            color: #d63031;
        }

        .badge-menunggudikirim {
            background-color: #d1f2eb;
            color: #00b894;
        }
        
        .badge-dikirim {
            background-color: #cce5ff;
            color: #0984e3;
        }
        
        .badge-selesai {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-dibatalkan {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .badge-dipinjam {
            background-color: #e1bee7;
            color: #8e24aa;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            gap: 5px;
        }
        
        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
        }
        
        .btn-info {
            background-color: var(--info);
            color: white;
        }
        
        .btn-info:hover {
            background-color: #3a7bd5;
        }
        
        .btn-secondary {
            background-color: var(--gray);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .btn-group {
            display: flex;
            gap: 8px;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 25px;
            border-radius: 10px;
            width: 450px;
            max-width: 90%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: slideDown 0.3s;
        }
        
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-header h3 {
            color: var(--dark);
            font-size: 20px;
            font-weight: 600;
        }
        
        .modal-header .close {
            cursor: pointer;
            font-size: 24px;
            color: var(--gray);
            transition: var(--transition);
            background: none;
            border: none;
        }
        
        .modal-header .close:hover {
            color: var(--danger);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 14px;
        }
        
        /* Alert styles */
        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        
        .info-box {
            background-color: #e8f4f8;
            border: 1px solid #17a2b8;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-box i {
            color: #17a2b8;
            margin-right: 10px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-header h3,
            .sidebar-menu li a span {
                display: none;
            }
            
            .sidebar-menu li a {
                justify-content: center;
                padding: 12px 0;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }
        
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .form-control {
                width: 100%;
            }
            
            th, td {
                padding: 8px 10px;
                font-size: 13px;
            }
            
            .modal-content {
                width: 95%;
                margin: 15% auto;
            }
        }
        
        @media (max-width: 576px) {
            .btn-group {
                flex-direction: column;
                gap: 5px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-mobile-alt"></i>
                <h3>Fanzzervice</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="./order.php" class="active"><i class="fas fa-shopping-cart"></i> Pesanan</a></li>
                <li><a href="products.php"><i class="fas fa-mobile"></i> Produk</a></li>
                <li><a href="pelanggan.php"><i class="fas fa-users"></i> Pelanggan</a></li>
                <li><a href="./logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2>Kelola Pesanan</h2>
                <div class="user-info">
                    <!-- User info -->
                </div>
            </div>
            
            <!-- Alert messages -->
            <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <strong>Cara Kerja Sistem:</strong> 
                <ul style="margin: 10px 0 0 30px;">
                    <li>Pesanan dibuat dengan status <strong>Pending</strong> (stok belum dikurangi)</li>
                    <li>Ubah ke <strong>Menunggu Pembayaran</strong> untuk mengurangi stok</li>
                    <li>Status <strong>Selesai</strong> atau <strong>Dibatalkan</strong> akan mengembalikan stok</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Daftar Pesanan</h3>
                    <div>
                        <select id="statusFilter" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="menunggupembayaran">Menunggu Pembayaran</option>
                            <option value="menunggudikirim">Menunggu Dikirim</option>
                            <option value="dikirim">Dikirim</option>
                            <option value="selesai">Selesai</option>
                            <option value="dibatalkan">Dibatalkan</option>
                            <option value="dipinjam">Dipinjam</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pelanggan</th>
                                <th>Produk</th>
                                <th>Tgl Sewa</th>
                                <th>Tgl Kembali</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>ID Transaksi</th>
                                <th>Kode Pengiriman</th>
                                <th>Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
                            $where = $status ? "WHERE o.status = '$status'" : "";

                            $query = "SELECT o.id, u.name as customer, p.nama_produk as product, 
                                     o.tanggal_sewa, o.tanggal_kembali, o.total_harga, o.status, o.shipping_code,
                                     pay.transaction_id
                                     FROM orders o
                                     JOIN users u ON o.user_id = u.id
                                     JOIN produk p ON o.produk_id = p.id
                                     LEFT JOIN payments pay ON o.id = pay.order_id
                                     $where
                                     ORDER BY o.created_at DESC";
                            $result = mysqli_query($conn, $query);

                            if(mysqli_num_rows($result) > 0) {
                                while ($order = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer']); ?></td>
                                <td><?php echo htmlspecialchars($order['product']); ?></td>
                                <td><?php echo date('d M Y', strtotime($order['tanggal_sewa'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($order['tanggal_kembali'])); ?></td>
                                <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower(str_replace(' ', '', $order['status'])); ?>">
                                        <?php 
                                        $status_display = [
                                            'pending' => 'Pending',
                                            'menunggupembayaran' => 'Menunggu Pembayaran',
                                            'menunggudikirim' => 'Menunggu Dikirim',
                                            'dikirim' => 'Dikirim',
                                            'dipinjam' => 'Dipinjam',
                                            'selesai' => 'Selesai',
                                            'dibatalkan' => 'Dibatalkan'
                                        ];
                                        echo $status_display[$order['status']] ?? ucfirst($order['status']);
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $order['transaction_id'] ? htmlspecialchars($order['transaction_id']) : '-'; ?>
                                </td>
                                <td>
                                    <?php echo ($order['shipping_code'] && $order['status'] === 'dikirim') ? htmlspecialchars($order['shipping_code']) : '-'; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-info" onclick="openStatusModal(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')" title="Ubah Status">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            } else {
                                echo '<tr><td colspan="10" style="text-align: center;">Tidak ada pesanan ditemukan</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ubah Status Pesanan</h3>
                <span onclick="closeStatusModal()" class="close">&times;</span>
            </div>
            <form id="statusForm" method="post" action="update_status.php">
                <input type="hidden" name="order_id" id="modalOrderId">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="modalStatus" class="form-control" required>
                        <option value="pending">Pending</option>
                        <option value="menunggupembayaran">Menunggu Pembayaran</option>
                        <option value="menunggudikirim">Menunggu Dikirim</option>
                        <option value="dikirim">Dikirim</option>
                        <option value="dipinjam">Dipinjam</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
                <div class="form-group" id="shippingCodeGroup">
                    <label for="shipping_code">Kode Pengiriman</label>
                    <input type="text" name="shipping_code" id="shipping_code" class="form-control" placeholder="Masukkan kode pengiriman">
                </div>
                <div class="form-group" style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const statusParam = urlParams.get('status');
            if (statusParam) {
                document.getElementById('statusFilter').value = statusParam;
            }

            document.getElementById('shippingCodeGroup').style.display = 'none';
        });

        document.getElementById('statusFilter').addEventListener('change', function() {
            const status = this.value;
            window.location.href = `order.php${status ? '?status=' + encodeURIComponent(status) : ''}`;
        });

        function openStatusModal(orderId, currentStatus) {
            document.getElementById('modalOrderId').value = orderId;
            document.getElementById('modalStatus').value = currentStatus;
            document.getElementById('statusModal').style.display = 'block';
            toggleShippingCodeField(currentStatus);
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        function toggleShippingCodeField(status) {
            const shippingCodeGroup = document.getElementById('shippingCodeGroup');
            if (status === 'dikirim') {
                shippingCodeGroup.style.display = 'block';
            } else {
                shippingCodeGroup.style.display = 'none';
                document.getElementById('shipping_code').value = '';
            }
        }

        window.onclick = function(event) {
            const modal = document.getElementById('statusModal');
            if (event.target === modal) {
                closeStatusModal();
            }
        }

        document.getElementById('modalStatus').addEventListener('change', function() {
            toggleShippingCodeField(this.value);
        });
    </script>
</body>
</html>