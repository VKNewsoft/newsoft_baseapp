<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak</title>
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-light: #3b82f6;
            --text: #1f2937;
            --text-light: #6b7280;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 0;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 600px;
            width: 90%;
            text-align: center;
            padding: 2.5rem;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            animation: fadeIn 0.6s ease-out;
        }

        .icon {
            font-size: 60px;
            color: var(--primary);
            margin-bottom: 1rem;
            display: inline-block;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0 0 1rem 0;
        }

        p {
            font-size: 1.1rem;
            color: var(--text-light);
            margin: 1.2rem 0;
        }

        .ip-box {
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            background: #f1f5f9;
            border: 1px dashed var(--primary-light);
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
            display: inline-block;
            margin: 1rem auto;
            color: #0f172a;
            font-weight: bold;
        }

        .message {
            background-color: #fef7ed;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            text-align: left;
            border-radius: 6px;
            font-size: 0.95rem;
            color: #7c2d12;
            margin: 1.5rem auto;
            max-width: 450px;
        }

        .footer {
            margin-top: 2rem;
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .footer a {
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 500;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 600px) {
            .container {
                padding: 2rem;
            }
            h1 {
                font-size: 2rem;
            }
            .ip-box {
                font-size: 1rem;
            }
        }
    </style>
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="icon">
            <i class="fas fa-ban"></i>
        </div>

        <h1>403 - Akses Ditolak</h1>

        <p>
            <?php if (!empty($message) && $message !== '(null)'): ?>
                <?= esc($message) ?>
            <?php else: ?>
                Maaf! Anda terlalu sering mengakses halaman ini.
            <?php endif ?>
        </p>

        <div>Alamat IP Anda:</div>
        <div class="ip-box">
            <?= esc(service('request')->getIPAddress()) ?>
        </div>

        <div class="message">
            <strong>Informasi Keamanan:</strong> Akses Anda diblokir karena aktivitas mencurigakan seperti serangan otomatis atau permintaan berlebihan. Sistem kami mendeteksi potensi ancaman dari alamat IP ini.
        </div>

        <div class="footer">
            &copy; <?= date('Y') ?> Sistem Keamanan. Untuk bantuan, hubungi tim terkait
        </div>
    </div>
</body>
</html>