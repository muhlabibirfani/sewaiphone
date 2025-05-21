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
        
        .sidebar {
            width: 250px;
            background-color: var(--white);
            box-shadow: var(--shadow);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            transition: var(--transition);
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-header i {
            font-size: 24px;
            color: var(--primary);
        }
        
        .sidebar-header h3 {
            color: var(--primary);
            font-size: 18px;
            font-weight: 600;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--dark);
            text-decoration: none;
            transition: var(--transition);
            font-size: 14px;
        }
        
        .sidebar-menu li a:hover, 
        .sidebar-menu li a.active {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            border-left: 3px solid var(--primary);
            padding-left: 17px;
        }
        
        .sidebar-menu li a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
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
            background-color: #ffeeba;
            color: #856404;
        }

        .badge-menunggukonfirmasi {
            background-color:rgb(215, 245, 186);
            color:rgb(90, 179, 7);
        }
        
        .badge-dikirim {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-selesai {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .badge-dibatalkan {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .badge-dipinjam {
            background-color: #d1ecf1;
            color: #0c5460;
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
                <li><a href="./order.php"  class="active"><i class="fas fa-shopping-cart"></i> Pesanan</a></li>
                <li><a href="products.php"><i class="fas fa-mobile"></i> Produk</a></li>
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

            <div class="card">
                <div class="card-header">
                    <h3>Daftar Pesanan</h3>
                    <div>
                        <select id="statusFilter" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="menunggupembayaran">Menunggu Pembayaran</option>
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
                                <th>Kode Pengiriman</th>
                                <th>Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
                            $where = $status ? "WHERE o.status = '$status'" : "";

                            $query = "SELECT o.id, u.name as customer, p.nama_produk as product, 
                                     o.tanggal_sewa, o.tanggal_kembali, o.total_harga, o.status, o.shipping_code
                                     FROM orders o
                                     JOIN users u ON o.user_id = u.id
                                     JOIN produk p ON o.produk_id = p.id
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
                                    <span class="badge badge-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
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
                                echo '<tr><td colspan="9" style="text-align: center;">Tidak ada pesanan ditemukan</td></tr>';
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
                <span onclick="closeStatusModal()">&times;</span>
            </div>
            <form id="statusForm" method="post" action="update_status.php">
                <input type="hidden" name="order_id" id="modalOrderId">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="modalStatus" class="form-control" required>
                        <option value="pending">Pending</option>
                        <option value="menunggupembayaran">Menunggu Pembayaran</option>
                        <option value="menunggukonfirmasi">Menunggu Konfirmasi</option>
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
            window.location.href = `orders.php${status ? '?status=' + encodeURIComponent(status) : ''}`;
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
