<?php
include 'config.php';

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // Validate email
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email tidak valid';
    } else {
        // Check if email exists
        $check_email = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
        if(mysqli_num_rows($check_email) > 0) {
            $error = 'Email sudah terdaftar';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $insert_query = "INSERT INTO users (name, email, password, phone, address) 
                            VALUES ('$name', '$email', '$hashed_password', '$phone', '$address')";
            
            if(mysqli_query($conn, $insert_query)) {
                $success = 'Registrasi berhasil! Silakan login';
                header("refresh:2; url=login.php");
            } else {
                $error = 'Terjadi kesalahan: ' . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Fanzzervice</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .auth-container {
            background: white;
            width: 100%;
            max-width: 500px;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-header h1 {
            color: #0071e3;
            margin-bottom: 10px;
        }
        
        .auth-header p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            font-size: 16px;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background-color: #0071e3;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background-color: #005bb5;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 20px;
        }
        
        .auth-footer a {
            color: #0071e3;
            text-decoration: none;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>Daftar Akun</h1>
            <p>Buat akun untuk mulai menyewa iPhone premium</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="phone">Nomor Telepon</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            
            <div class="form-group">
                <label for="address">Alamat</label>
                <input type="text" id="address" name="address" required>
            </div>
            
            <button type="submit" class="btn">Daftar Sekarang</button>
        </form>
        
        <div class="auth-footer">
            <p>Sudah punya akun? <a href="login.php">Login disini</a></p>
        </div>
    </div>
</body>
</html>