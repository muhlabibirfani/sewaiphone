<?php
include 'config.php';

// Create tables
$sql = [
    // Users table
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Products table
    "CREATE TABLE IF NOT EXISTS produk (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_produk VARCHAR(100) NOT NULL,
        deskripsi TEXT NOT NULL,
        harga_sewa DECIMAL(10,2) NOT NULL,
        gambar VARCHAR(255) NOT NULL,
        is_new BOOLEAN DEFAULT FALSE,
        stok INT DEFAULT 1,
        stok_tersedia INT DEFAULT 1,
        dp_percentage INT DEFAULT 30,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Orders table
    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        produk_id INT NOT NULL,
        user_id INT NOT NULL,
        tanggal_sewa DATE NOT NULL,
        tanggal_kembali DATE NOT NULL,
        total_harga DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'menunggupembayaran', 'menunggudikirim', 'dikirim', 'dipinjam', 'selesai', 'dibatalkan') DEFAULT 'pending',
        payment_method ENUM('transfer','qris') DEFAULT 'transfer',
        shipping_code VARCHAR(20) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (produk_id) REFERENCES produk(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",


    // Payments table
    "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        method ENUM('transfer','qris') NOT NULL,
        status ENUM('pending', 'menunggupembayaran', 'menunggudikirim','dikirim', 'dipinjam', 'selesai', 'dibatalkan') DEFAULT 'pending',
        payment_proof VARCHAR(255),
        transaction_id VARCHAR(100),
        payment_date DATETIME,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE,
        INDEX idx_order_id (order_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // Order approvals table
    "CREATE TABLE IF NOT EXISTS order_approvals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        admin_id INT NOT NULL,
        action ENUM('approve','reject') NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE,
        FOREIGN KEY (admin_id) REFERENCES users(id) ON UPDATE CASCADE,
        INDEX idx_order_id (order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Returns table
    "CREATE TABLE IF NOT EXISTS returns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        user_id INT NOT NULL,
        reason VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected', 'processed') DEFAULT 'pending',
        admin_notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Return logs table
    "CREATE TABLE IF NOT EXISTS return_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        return_id INT NOT NULL,
        user_id INT NULL,
        action VARCHAR(50) NOT NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (return_id) REFERENCES returns(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // Stock mutations table - VERSION TERBARU YANG SESUAI DENGAN PROGRAM
    "CREATE TABLE IF NOT EXISTS stock_mutations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        produk_id INT NOT NULL,
        order_id INT,
        return_id INT,
        type ENUM('in','out') NOT NULL COMMENT 'in untuk penambahan stok, out untuk pengurangan',
        quantity INT NOT NULL,
        reference_type ENUM('order','return','adjustment','other') NOT NULL,
        description VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (produk_id) REFERENCES produk(id) ON UPDATE CASCADE,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE,
        FOREIGN KEY (return_id) REFERENCES returns(id) ON UPDATE CASCADE,
        INDEX idx_produk_id (produk_id),
        INDEX idx_order_id (order_id),
        INDEX idx_return_id (return_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Order logs table
    "CREATE TABLE IF NOT EXISTS order_logs (
        id INT NOT NULL AUTO_INCREMENT,
        order_id INT NOT NULL,
        user_id INT DEFAULT NULL COMMENT 'Admin/user who made the change',
        action VARCHAR(255) NOT NULL COMMENT 'Description of the change',
        notes TEXT DEFAULT NULL COMMENT 'Additional details',
        status_from VARCHAR(50) DEFAULT NULL COMMENT 'Previous status',
        status_to VARCHAR(50) DEFAULT NULL COMMENT 'New status',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY order_id (order_id),
        KEY user_id (user_id),
        CONSTRAINT order_logs_ibfk_1 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
        CONSTRAINT order_logs_ibfk_2 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($sql as $query) {
    if (!mysqli_query($conn, $query)) {
        die("Error creating table: " . mysqli_error($conn));
    }
}

// Insert sample products
$check_products = mysqli_query($conn, "SELECT COUNT(*) as count FROM produk");
$row = mysqli_fetch_assoc($check_products);

if ($row['count'] == 0) {
    $sample_products = [
        "INSERT INTO produk (nama_produk, deskripsi, harga_sewa, gambar, is_new, stok, stok_tersedia, created_at) VALUES
        ('iPhone 16 Pro Max', 'iPhone 16 Pro Max', 450000.00, 'iphone16.png', 1, 2, 2, '2025-05-06 09:00:48');",
        
        "INSERT INTO produk (nama_produk, deskripsi, harga_sewa, gambar, is_new, stok, stok_tersedia, created_at) VALUES
        ('iPhone 15 Pro Max', 'iPhone 15 Pro Max', 250000.00, 'iphone15.jpg', 0, 3, 3, '2025-05-09 09:06:25');",
        
        "INSERT INTO produk (nama_produk, deskripsi, harga_sewa, gambar, is_new, stok, stok_tersedia, created_at) VALUES
        ('iPhone 14 Pro Max', 'iPhone 14 Pro Max', 200000.00, 'iphone14.jpg', 0, 5, 5, '2025-05-09 09:06:25');",
        
        "INSERT INTO produk (nama_produk, deskripsi, harga_sewa, gambar, is_new, stok, stok_tersedia, created_at) VALUES
        ('iPhone 13', 'iPhone 13 Pro', 150000.00, 'iphone13.jpg', 0, 4, 4, '2025-05-09 09:06:25');"
    ];

    foreach ($sample_products as $product_query) {
        if (!mysqli_query($conn, $product_query)) {
            die("Error inserting products: " . mysqli_error($conn));
        }
    }
}

// Create admin user if not exists
$check_admin = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE email = 'admin@fanzzervice.com'");
$admin = mysqli_fetch_assoc($check_admin);

if ($admin['count'] == 0) {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@fanzzervice.com', '$password', 'admin')");
}

// Migrasi untuk tabel stock_mutations jika sudah ada versi lama
$check_mutation_column = mysqli_query($conn, "SHOW COLUMNS FROM stock_mutations LIKE 'mutation_type'");
if (mysqli_num_rows($check_mutation_column) > 0) {
    // Jika ada kolom mutation_type (struktur lama), lakukan migrasi
    mysqli_query($conn, "ALTER TABLE stock_mutations 
                        CHANGE COLUMN mutation_type type ENUM('in','out') NOT NULL COMMENT 'in untuk penambahan stok, out untuk pengurangan',
                        ADD COLUMN return_id INT NULL AFTER order_id,
                        ADD COLUMN reference_type ENUM('order','return','adjustment','other') NOT NULL AFTER quantity,
                        ADD FOREIGN KEY (return_id) REFERENCES returns(id) ON UPDATE CASCADE,
                        ADD INDEX idx_return_id (return_id)");
    
    // Update existing records
    mysqli_query($conn, "UPDATE stock_mutations SET reference_type = 'order' WHERE type = 'out'");
    mysqli_query($conn, "UPDATE stock_mutations SET reference_type = 'adjustment' WHERE type = 'in'");
}

echo "Database setup successfully with all tables! You can now access the website.";
?>