<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Installer - Base App Admin - By Newsoft Developer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .installer-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }

        .installer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .installer-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .installer-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .installer-body {
            padding: 40px;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }

        .alert-info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            color: #004085;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group label .required {
            color: #e74c3c;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
        }

        .error-text {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #667eea;
            margin-bottom: 25px;
        }

        .info-box h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
        }

        .info-box ul {
            margin-left: 20px;
            font-size: 13px;
            color: #666;
        }

        .info-box ul li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <h1>🗄️ Database Installer</h1>
            <p>Base App Admin - Newsoft Developer</p>
        </div>

        <div class="installer-body">
            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger">
                    <strong>Error!</strong> <?= esc(session('error')) ?>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <h3>📋 Informasi Instalasi</h3>
                <ul>
                    <li>Proses instalasi akan membuat database baru</li>
                    <li>Import struktur dan data dari <strong>newsoft_base.sql</strong></li>
                    <li>Konfigurasi akan disimpan di <strong>app/Config/Database.php</strong></li>
                    <li>Pastikan MySQL server sudah berjalan</li>
                </ul>
            </div>

            <form method="post" action="<?= base_url('installer/install') ?>">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label>Database Host <span class="required">*</span></label>
                    <input type="text" name="db_host" value="<?= old('db_host', 'localhost') ?>" required>
                    <small>Biasanya: localhost atau 127.0.0.1</small>
                    <?php if (session('errors.db_host')): ?>
                        <div class="error-text"><?= session('errors.db_host') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Database Port <span class="required">*</span></label>
                    <input type="number" name="db_port" value="<?= old('db_port', '3306') ?>" required>
                    <small>Port default MySQL: 3306</small>
                    <?php if (session('errors.db_port')): ?>
                        <div class="error-text"><?= session('errors.db_port') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Database Username <span class="required">*</span></label>
                    <input type="text" name="db_username" value="<?= old('db_username', 'root') ?>" required>
                    <small>Username untuk akses MySQL</small>
                    <?php if (session('errors.db_username')): ?>
                        <div class="error-text"><?= session('errors.db_username') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Database Password</label>
                    <input type="password" name="db_password" value="<?= old('db_password', '') ?>">
                    <small>Kosongkan jika tidak ada password (default XAMPP)</small>
                    <?php if (session('errors.db_password')): ?>
                        <div class="error-text"><?= session('errors.db_password') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Nama Database <span class="required">*</span></label>
                    <input type="text" name="db_name" value="<?= old('db_name', 'newsoft_app') ?>" required>
                    <small>Database akan dibuat otomatis jika belum ada</small>
                    <?php if (session('errors.db_name')): ?>
                        <div class="error-text"><?= session('errors.db_name') ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn">
                    🚀 Install Database
                </button>
            </form>
        </div>
    </div>
</body>
</html>
