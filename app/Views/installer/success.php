<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi Berhasil - Base App Admin - By Newsoft Developer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            text-align: center;
            padding: 50px 40px;
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 1s ease;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }

        h1 {
            color: #11998e;
            font-size: 32px;
            margin-bottom: 15px;
        }

        p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: left;
        }

        .info-box h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-box ul {
            list-style: none;
        }

        .info-box li {
            padding: 8px 0;
            color: #555;
            font-size: 14px;
            display: flex;
            align-items: start;
            gap: 10px;
        }

        .info-box li::before {
            content: "✅";
            flex-shrink: 0;
        }

        .credentials {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .credentials h3 {
            color: #856404;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .credentials p {
            margin: 5px 0;
            color: #856404;
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        .btn {
            display: inline-block;
            padding: 14px 40px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(17, 153, 142, 0.4);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">🎉</div>
        
        <h1>Instalasi Berhasil!</h1>
        <p>Database telah berhasil diinstall dan dikonfigurasi dengan lengkap.</p>

        <div class="info-box">
            <h3>📦 Data yang Terinstall:</h3>
            <ul>
                <li><strong>34 Tabel</strong> struktur database lengkap</li>
                <li><strong>1 User Admin</strong> untuk login pertama kali</li>
                <li><strong>141 Bank</strong> data bank Indonesia</li>
                <li><strong>82,503 Kelurahan</strong> data wilayah lengkap</li>
                <li><strong>Menu & Role</strong> konfigurasi akses default</li>
            </ul>
        </div>

        <div class="credentials">
            <h3>🔐 Kredensial Login Default:</h3>
            <p>Username: <strong>admin</strong></p>
            <p>Password: <strong>123456</strong></p>
        </div>

        <a href="<?= base_url('/') ?>" class="btn">🚀 Masuk ke Aplikasi</a>
    </div>
</body>
</html>
