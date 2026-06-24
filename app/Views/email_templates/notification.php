<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
            color: #333333;
            line-height: 1.6;
        }
        .content h2 {
            color: #2c3e50;
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .content p {
            margin-bottom: 15px;
            font-size: 16px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #555;
        }
        .cta-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3498db;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .cta-button:hover {
            background-color: #2980b9;
        }
        .footer {
            background-color: #ecf0f1;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #7f8c8d;
        }
        .footer p {
            margin: 5px 0;
        }
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1><?= esc($app_name) ?></h1>
        </div>

        <!-- Content -->
        <div class="content">
            <h2><?= esc($title) ?></h2>
            
            <p>Halo <?= esc($user['name'] ?? 'Pengguna') ?>,</p>
            
            <p><?= nl2br(esc($message)) ?></p>

            <?php if (!empty($data['url'])): ?>
                <div class="info-box">
                    <p><strong>Informasi Tambahan:</strong></p>
                    <p>Klik tombol di bawah ini untuk melihat detail lengkap:</p>
                </div>
                
                <div style="text-align: center;">
                    <a href="<?= esc($data['url']) ?>" class="cta-button">Lihat Detail</a>
                </div>
            <?php endif; ?>

            <div class="divider"></div>
            
            <p style="font-size: 14px; color: #7f8c8d;">
                Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi tim dukungan kami.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?= date('Y') ?> <?= esc($app_name) ?>. Hak cipta dilindungi.</p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
            <p>
                Ingin mengubah preferensi notifikasi? 
                <a href="<?= site_url('notifications/settings') ?>" style="color: #3498db;">Kelola di sini</a>
            </p>
        </div>
    </div>
</body>
</html>
