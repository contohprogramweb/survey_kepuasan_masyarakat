<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - Survey Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #3498db;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        .switch-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .switch-label {
            flex: 1;
        }
        .switch-label h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .switch-label p {
            font-size: 13px;
            color: #7f8c8d;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #3498db;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
        }
        .info-box h4 {
            color: #1976d2;
            margin-bottom: 10px;
        }
        .info-box p {
            color: #424242;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= esc($title) ?></h1>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-error">
                <ul style="margin: 10px 0 0 20px;">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <h4>ℹ️ Tentang Pengaturan Notifikasi</h4>
            <p>Kelola preferensi notifikasi Anda untuk menerima pemberitahuan melalui berbagai saluran. Notifikasi in-app akan selalu muncul di dashboard ketika ada pembaruan penting seperti periode survei yang akan berakhir, target responden yang belum tercapai, atau penurunan IKM.</p>
        </div>

        <?= form_open('notifications/update-settings') ?>
            <div class="form-group">
                <div class="switch-container">
                    <div class="switch-label">
                        <h3>🔔 Notifikasi In-App</h3>
                        <p>Tampilkan badge dan daftar notifikasi di dashboard</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="enable_inapp" value="1" <?= $preferences['enable_inapp'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="switch-container">
                    <div class="switch-label">
                        <h3>📧 Notifikasi Email</h3>
                        <p>Kirim notifikasi ke alamat email terdaftar</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="enable_email" value="1" <?= $preferences['enable_email'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="switch-container">
                    <div class="switch-label">
                        <h3>💬 Notifikasi WhatsApp</h3>
                        <p>Kirim notifikasi ke nomor WhatsApp terdaftar (memerlukan template yang disetujui)</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="enable_whatsapp" value="1" <?= $preferences['enable_whatsapp'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-top: 30px;">
                <button type="submit" class="btn">Simpan Pengaturan</button>
            </div>
        <?= form_close() ?>
    </div>
</body>
</html>
