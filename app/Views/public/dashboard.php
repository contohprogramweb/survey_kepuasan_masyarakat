<!DOCTYPE html>
<html lang="<?= $currentLocale ?? 'id' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title><?= esc($title ?? 'Dashboard Transparansi IKM') ?></title>
    <meta name="description" content="<?= __lang('dashboard_description') ?>">
    <meta name="keywords" content="<?= __lang('page_keywords') ?>">
    <link rel="canonical" href="<?= current_url() ?>">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:title" content="<?= esc($title ?? 'Dashboard Transparansi IKM') ?>">
    <meta property="og:description" content="<?= __lang('dashboard_description') ?>">
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "<?= esc($title ?? 'Dashboard Transparansi IKM') ?>",
        "description": "<?= __lang('dashboard_description') ?>",
        "url": "<?= current_url() ?>"
    }
    </script>
    
    <!-- CDN Resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--bg-light);
        }
        
        a:focus, button:focus {
            outline: 3px solid var(--primary-color);
            outline-offset: 2px;
        }
        
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            z-index: 10000;
        }
        
        .skip-link:focus {
            top: 0;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .dashboard-content {
            padding: 60px 0;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .stat-card p {
            color: var(--text-light);
        }
        
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link"><?= __lang('skip_to_main_content') ?></a>
    
    <header class="dashboard-header">
        <div class="container">
            <h1><?= esc($title ?? 'Dashboard Transparansi IKM') ?></h1>
            <p><?= __lang('dashboard_subtitle') ?></p>
        </div>
    </header>
    
    <main id="main-content" role="main">
        <section class="dashboard-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="stat-card">
                            <h3>-</h3>
                            <p><?= __lang('total_respondents') ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="stat-card">
                            <h3>-</h3>
                            <p><?= __lang('average_score') ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="stat-card">
                            <h3>-</h3>
                            <p><?= __lang('satisfaction_rate') ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-5">
                    <a href="<?= base_url() ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i><?= __lang('back_to_home') ?>
                    </a>
                </div>
            </div>
        </section>
    </main>
    
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= __lang('all_rights_reserved') ?></p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
