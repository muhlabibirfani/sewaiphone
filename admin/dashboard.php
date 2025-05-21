<?php include '../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Fanzzervice</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #0071e3;
            --primary-dark: #005bb5;
            --secondary: #f5f5f7;
            --dark: #1d1d1f;
            --light: #f5f5f5;
            --gray: #86868b;
            --white: #ffffff;
            --danger: #dc3545;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: var(--dark);
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--white);
            box-shadow: var(--shadow);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        
        .sidebar-header h3 {
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: var(--dark);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .sidebar-menu li a:hover, 
        .sidebar-menu li a.active {
            background-color: rgba(0, 113, 227, 0.1);
            color: var(--primary);
        }
        
        .sidebar-menu li a i {
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .header h2 {
            color: var(--dark);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* Cards */
        .card {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .card-header h3 {
            color: var(--dark);
        }
        
        /* Table */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: var(--secondary);
            font-weight: 600;
        }
        
        tr:hover {
            background-color: rgba(0, 113, 227, 0.05);
        }
        
        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #d39e00;
        }
        
        .badge-diproses {
            background-color: rgba(23, 162, 184, 0.2);
            color: #117a8b;
        }
        
        .badge-dikirim {
            background-color: rgba(0, 123, 255, 0.2);
            color: #0062cc;
        }
        
        .badge-selesai {
            background-color: rgba(40, 167, 69, 0.2);
            color: #1e7e34;
        }
        
        .badge-dibatalkan {
            background-color: rgba(220, 53, 69, 0.2);
            color: #bd2130;
        }
        
        /* Buttons */
        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: var(--dark);
        }
        
        .btn-info {
            background-color: var(--info);
            color: white;
        }
        
        /* Form */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.2);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
            }
            
            .main-content {
                margin-left: 200px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
                transition: var(--transition);
            }
            
            .sidebar.active {
                width: 250px;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-mobile-alt"></i> Fanzzervice</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="order.php"><i class="fas fa-shopping-cart"></i> Pesanan</a></li>
                <li><a href="products.php"><i class="fas fa-mobile"></i> Produk</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2>Dashboard Admin</h2>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                    <span><?php echo $_SESSION['user_name']; ?></span>
                    <button class="btn btn-sm btn-primary mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <?php
                $queries = [
                    'total_orders' => "SELECT COUNT(*) as total FROM orders",
                    'pending_orders' => "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'",
                    'processed_orders' => "SELECT COUNT(*) as total FROM orders WHERE status = 'diproses'",
                    'completed_orders' => "SELECT COUNT(*) as total FROM orders WHERE status = 'selesai'"
                ];
                
                $stats = [];
                foreach ($queries as $key => $query) {
                    $result = mysqli_query($conn, $query);
                    $stats[$key] = mysqli_fetch_assoc($result)['total'];
                }
                ?>
                
                <div class="card">
                    <h3>Total Pesanan</h3>
                    <p style="font-size: 24px; font-weight: bold; color: var(--primary);"><?php echo $stats['total_orders']; ?></p>
                </div>
                
                <div class="card">
                    <h3>Pending</h3>
                    <p style="font-size: 24px; font-weight: bold; color: var(--warning);"><?php echo $stats['pending_orders']; ?></p>
                </div>
                
                <div class="card">
                    <h3>Diproses</h3>
                    <p style="font-size: 24px; font-weight: bold; color: var(--info);"><?php echo $stats['processed_orders']; ?></p>
                </div>
                
                <div class="card">
                    <h3>Selesai</h3>
                    <p style="font-size: 24px; font-weight: bold; color: var(--success);"><?php echo $stats['completed_orders']; ?></p>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header">
                    <h3>Pesanan Terbaru</h3>
                    <a href="order.php" class="btn btn-primary">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pelanggan</th>
                                <th>Produk</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT o.id, u.name as customer, p.nama_produk as produk, o.tanggal_sewa, o.total_harga, o.status 
                                      FROM orders o
                                      JOIN users u ON o.user_id = u.id
                                      JOIN produk p ON o.produk_id = p.id
                                      ORDER BY o.created_at DESC LIMIT 5";
                            $result = mysqli_query($conn, $query);
                            
                            while ($order = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo $order['customer']; ?></td>
                                <td><?php echo $order['produk']; ?></td>
                                <td><?php echo date('d M Y', strtotime($order['tanggal_sewa'])); ?></td>
                                <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>