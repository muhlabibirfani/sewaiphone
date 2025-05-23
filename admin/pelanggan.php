<?php
include '../config.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

// Handle customer deletion
if (isset($_GET['delete'])) {
    $customer_id = intval($_GET['delete']);
    
    // First check if customer exists
    $check_query = "SELECT id FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Delete customer
        $delete_query = "DELETE FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $customer_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Pelanggan berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus pelanggan: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Pelanggan tidak ditemukan!";
    }
    
    header("Location: pelanggan.php");
    exit;
}

// Get all customers (users with role 'user')
$query = "SELECT id, name, email, address, phone, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pelanggan | Fanzzervice</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }
        
        /* Admin container */
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
        
        /* Main content */
        .main-content {
            flex: 1;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }
        
        /* Card */
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Table */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            font-weight: 400;
            color: #212529;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            background-color: transparent;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s, background-color 0.15s, border-color 0.15s;
            text-decoration: none;
        }
        
        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .btn-danger {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .btn-info {
            color: #fff;
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
        
        /* Customer specific */
        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #28a745;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .customer-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Alert styles */
        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 5px;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
                position: fixed;
                height: 100%;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block !important;
            }
            
            .table-responsive {
                font-size: 14px;
            }
            
            .customer-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
        
        .mobile-menu-btn {
            display: none;
        }
        
        /* Statistics cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-card i {
            font-size: 24px;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-mobile-alt"></i>
                <h3>Fanzzervice</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="./order.php"><i class="fas fa-shopping-cart"></i> Pesanan</a></li>
                <li><a href="products.php"><i class="fas fa-mobile"></i> Produk</a></li>
                <li><a href="pelanggan.php" class="active"><i class="fas fa-users"></i> Pelanggan</a></li>
                <li><a href="./logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2>Kelola Pelanggan</h2>
                <button class="btn btn-primary mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
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
            
            <!-- Statistics -->
            <div class="stats-container">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="stat-number"><?php echo mysqli_num_rows($result); ?></div>
                    <div class="stat-label">Total Pelanggan</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-plus"></i>
                    <div class="stat-number">
                        <?php 
                        $today_query = "SELECT COUNT(*) as today_count FROM users WHERE role = 'user' AND DATE(created_at) = CURDATE()";
                        $today_result = mysqli_query($conn, $today_query);
                        $today_data = mysqli_fetch_assoc($today_result);
                        echo $today_data['today_count'];
                        ?>
                    </div>
                    <div class="stat-label">Pendaftar Hari Ini</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-week"></i>
                    <div class="stat-number">
                        <?php 
                        $week_query = "SELECT COUNT(*) as week_count FROM users WHERE role = 'user' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                        $week_result = mysqli_query($conn, $week_query);
                        $week_data = mysqli_fetch_assoc($week_result);
                        echo $week_data['week_count'];
                        ?>
                    </div>
                    <div class="stat-label">Pendaftar Minggu Ini</div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Pelanggan</h3>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Pelanggan</th>
                                <th>Email</th>
                                <th>No. Telepon</th>
                                <th>Alamat</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Reset result pointer
                            mysqli_data_seek($result, 0);
                            
                            if (mysqli_num_rows($result) > 0):
                                while ($customer = mysqli_fetch_assoc($result)): 
                                    // Create avatar initial from name
                                    $name_parts = explode(' ', $customer['name']);
                                    $initials = '';
                                    foreach ($name_parts as $part) {
                                        $initials .= strtoupper(substr($part, 0, 1));
                                        if (strlen($initials) >= 2) break;
                                    }
                                    if (strlen($initials) < 2 && strlen($customer['name']) > 0) {
                                        $initials = strtoupper(substr($customer['name'], 0, 2));
                                    }
                            ?>
                            <tr>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">
                                            <?php echo $initials; ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($customer['name']); ?></strong>
                                            <br>
                                            <small style="color: #666;">ID: <?php echo $customer['id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td>
                                    <?php if (!empty($customer['phone'])): ?>
                                        <a href="tel:<?php echo $customer['phone']; ?>" style="color: #007bff; text-decoration: none;">
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($customer['phone']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999;">Tidak ada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($customer['address'])): ?>
                                        <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($customer['address']); ?>">
                                            <?php echo htmlspecialchars($customer['address']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #999;">Belum diisi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <?php echo date('d M Y', strtotime($customer['created_at'])); ?>
                                        <br>
                                        <small style="color: #666;"><?php echo date('H:i', strtotime($customer['created_at'])); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="pelanggan.php?delete=<?php echo $customer['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini? Semua data terkait akan ikut terhapus.')" title="Hapus Pelanggan">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px; color: #999;">
                                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                    Belum ada pelanggan terdaftar
                                </td>
                            </tr>
                            <?php endif; ?>
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
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.getElementById('mobileMenuBtn');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>