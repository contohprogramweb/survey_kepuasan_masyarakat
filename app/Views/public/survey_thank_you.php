<!DOCTYPE html>
<html lang="<?= $currentLocale ?? 'id' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terima Kasih - Survei IKM</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .thank-you-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            padding: 60px 40px;
            max-width: 600px;
            text-align: center;
        }
        
        .success-icon {
            width: 120px;
            height: 120px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }
        
        .success-icon i {
            color: white;
            font-size: 60px;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        h1 {
            color: #28a745;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .message {
            color: #6c757d;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .unit-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .btn-home {
            background: #2563eb;
            color: white;
            padding: 12px 40px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-home:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            color: white;
        }
    </style>
</head>
<body>
    <div class="thank-you-card">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1>Terima Kasih!</h1>
        
        <p class="message">
            Partisipasi Anda sangat berarti bagi kami.<br>
            Masukan dari survei ini akan digunakan untuk meningkatkan kualitas pelayanan publik.
        </p>
        
        <?php if (!empty($unit)): ?>
        <div class="unit-info">
            <h5><i class="fas fa-building"></i> <?= esc($unit['nama_unit'] ?? '') ?></h5>
            <?php if (!empty($unit['alamat'])): ?>
            <p class="text-muted mb-0"><i class="fas fa-map-marker-alt"></i> <?= esc($unit['alamat']) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <a href="<?= base_url() ?>" class="btn-home">
            <i class="fas fa-home"></i> Kembali ke Beranda
        </a>
        
        <div class="mt-5 text-muted">
            <small>
                <i class="fas fa-shield-alt"></i> Data Anda dilindungi sesuai UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi
            </small>
        </div>
    </div>
</body>
</html>
