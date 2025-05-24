<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak | Fanzzervice</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .contact-section {
            padding: 80px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h1 {
            font-size: 36px;
            color: #0071e3;
            margin-bottom: 15px;
        }
        
        .section-title p {
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .contact-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
       .contact-info {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 113, 227, 0.1);
            border: 1px solid rgba(0, 113, 227, 0.05);
            transition: all 0.3s ease;
        }
        
        .contact-info:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 113, 227, 0.15);
        }
        
        .contact-info h2 {
            margin: 0 0 30px 0;
            color: #0071e3;
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }
        
        .contact-info h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #0071e3, #005bb5);
            border-radius: 2px;
        }
        
        .contact-items-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 12px;
            border-left: 4px solid #0071e3;
            transition: all 0.3s ease;
        }
        
        .contact-item:last-child {
            margin-bottom: 0;
        }
        
        .contact-item:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 113, 227, 0.1);
        }
        
        .contact-icon {
            font-size: 22px;
            color: #0071e3;
            margin-right: 20px;
            margin-top: 2px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 113, 227, 0.1);
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .contact-item:hover .contact-icon {
            background: #0071e3;
            color: white;
            transform: scale(1.1);
        }
        
        .contact-item h3 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }
        
        .contact-item p {
            margin: 0;
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        
        @media (max-width: 768px) {
            .contact-items-container {
                grid-template-columns: 1fr;
            }
        }
        
        .contact-form {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .contact-form h2 {
            margin-top: 0;
            color: #0071e3;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            font-size: 16px;
        }
        
        .form-group textarea {
            height: 150px;
        }
        
        .btn {
            padding: 12px 25px;
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
        
        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <section class="contact-section">
        <div class="container">
            <div class="section-title">
                <h1>Hubungi Kami</h1>
                <p>Punya pertanyaan atau butuh bantuan? Tim kami siap membantu Anda kapan saja.</p>
            </div>
            
           <div class="contact-info">
    <h2>Informasi Kontak</h2>
    
    <div class="contact-items-container">
        <div class="contact-column">
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div>
                    <h3>Alamat</h3>
                    <p>Jl. Mademulyo No. 20, Jawa Timur, Indonesia</p>
                </div>
            </div>
            
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <div>
                    <h3>Telepon</h3>
                    <p>+62 882 3573 3204</p>
                </div>
            </div>
        </div>
        
        <div class="contact-column">
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <h3>Email</h3>
                    <p>info@fanzzervice.com</p>
                </div>
            </div>
            
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3>Jam Operasional</h3>
                    <p>Senin - Minggu: 09.00 - 21.00 WIB</p>
                </div>
            </div>
        </div>
    </div>
</div>
        </div>
    </section>
</body>
</html>