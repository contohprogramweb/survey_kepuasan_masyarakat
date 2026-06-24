<!DOCTYPE html>
<html lang="<?= $currentLocale ?? 'id' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title><?= esc($seo['title'] ?? 'IKM Survey') ?></title>
    <meta name="description" content="<?= esc($seo['description'] ?? '') ?>">
    <meta name="keywords" content="<?= esc($seo['keywords'] ?? '') ?>">
    <meta name="author" content="<?= esc($instansi['nama'] ?? '') ?>">
    <link rel="canonical" href="<?= esc($seo['canonical'] ?? current_url()) ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= esc($seo['canonical'] ?? current_url()) ?>">
    <meta property="og:title" content="<?= esc($seo['title'] ?? '') ?>">
    <meta property="og:description" content="<?= esc($seo['description'] ?? '') ?>">
    <meta property="og:image" content="<?= esc($seo['og_image'] ?? '') ?>">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= esc($seo['canonical'] ?? current_url()) ?>">
    <meta property="twitter:title" content="<?= esc($seo['title'] ?? '') ?>">
    <meta property="twitter:description" content="<?= esc($seo['description'] ?? '') ?>">
    <meta property="twitter:image" content="<?= esc($seo['og_image'] ?? '') ?>">
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
        <?= $jsonLd ?? '{}' ?>
    </script>
    
    <!-- CDN Resources for Performance -->
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Font Awesome (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--bg-light);
        }
        
        /* Accessibility: Focus states */
        a:focus, button:focus, input:focus, select:focus {
            outline: 3px solid var(--accent-color);
            outline-offset: 2px;
        }
        
        /* Skip to main content link for accessibility */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            z-index: 10000;
            transition: top 0.3s;
        }
        
        .skip-link:focus {
            top: 0;
        }
        
        /* Header Styles */
        .site-header {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color);
        }
        
        .navbar-brand img {
            height: 40px;
            width: auto;
            margin-right: 10px;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .hero-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .hero-section p {
            font-size: 1.25rem;
            opacity: 0.9;
            max-width: 800px;
            margin: 0 auto 30px;
        }
        
        /* Unit Layanan Cards */
        .unit-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        
        .unit-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        
        .unit-card h3 {
            font-size: 1.5rem;
            color: var(--text-dark);
            margin-bottom: 12px;
        }
        
        .unit-card p {
            color: var(--text-light);
            margin-bottom: 20px;
        }
        
        .btn-survei {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
            border: none;
        }
        
        .btn-survei:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        /* Dashboard Link */
        .dashboard-link {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin-top: 60px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .dashboard-link h2 {
            font-size: 1.75rem;
            margin-bottom: 16px;
            color: var(--text-dark);
        }
        
        .btn-dashboard {
            background-color: #10b981;
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        
        .btn-dashboard:hover {
            background-color: #059669;
            color: white;
        }
        
        /* Footer */
        .site-footer {
            background: var(--text-dark);
            color: white;
            padding: 60px 0 30px;
            margin-top: 80px;
        }
        
        .footer-content {
            margin-bottom: 30px;
        }
        
        .footer-content h4 {
            font-size: 1.25rem;
            margin-bottom: 20px;
        }
        
        .footer-content ul {
            list-style: none;
            padding: 0;
        }
        
        .footer-content ul li {
            margin-bottom: 10px;
        }
        
        .footer-content a {
            color: #d1d5db;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-content a:hover {
            color: white;
        }
        
        .copyright {
            border-top: 1px solid #374151;
            padding-top: 30px;
            text-align: center;
            color: #9ca3af;
        }
        
        /* Language Switcher */
        .language-switcher {
            display: flex;
            gap: 8px;
        }
        
        .lang-btn {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }
        
        .lang-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .lang-btn:hover:not(.active) {
            background: #f3f4f6;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
            }
            
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .hero-section p {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 576px) {
            .hero-section h1 {
                font-size: 1.75rem;
            }
            
            .unit-card {
                padding: 20px;
            }
        }
        
        /* High Contrast Mode Support */
        @media (prefers-contrast: high) {
            .unit-card {
                border: 2px solid var(--text-dark);
            }
            
            .btn-survei, .btn-dashboard {
                border: 2px solid currentColor;
            }
        }
        
        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link"><?= __lang('skip_to_main_content') ?></a>
    
    <!-- Header -->
    <header class="site-header" role="banner">
        <nav class="navbar navbar-expand-lg navbar-light" aria-label="<?= __lang('main_navigation') ?>">
            <div class="container">
                <a class="navbar-brand" href="<?= base_url() ?>">
                    <?php if (!empty($instansi['logo'])): ?>
                        <img src="<?= base_url('uploads/' . $instansi['logo']) ?>" alt="<?= esc($instansi['nama']) ?> Logo" height="40">
                    <?php endif; ?>
                    <?= esc($instansi['nama']) ?>
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                        aria-controls="navbarNav" aria-expanded="false" aria-label="<?= __lang('toggle_navigation') ?>">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav align-items-center">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard') ?>"><?= __lang('dashboard') ?></a>
                        </li>
                        <li class="nav-item ms-3">
                            <div class="language-switcher" role="group" aria-label="<?= __lang('language_selection') ?>">
                                <?php foreach ($supportedLocales as $code => $name): ?>
                                    <a href="<?= base_url('language/' . $code) ?>" 
                                       class="lang-btn <?= $currentLocale === $code ? 'active' : '' ?>"
                                       aria-current="<?= $currentLocale === $code ? 'true' : 'false' ?>">
                                        <?= esc($name) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main id="main-content" role="main">
        <!-- Hero Section -->
        <section class="hero-section" aria-labelledby="hero-heading">
            <div class="container">
                <h1 id="hero-heading"><?= esc($instansi['nama']) ?></h1>
                <p><?= esc($instansi['deskripsi'] ?? __lang('welcome_message')) ?></p>
            </div>
        </section>

        <!-- Unit Layanan Section -->
        <section class="py-5" aria-labelledby="units-heading">
            <div class="container">
                <h2 id="units-heading" class="text-center mb-5"><?= __lang('available_services') ?></h2>
                
                <?php if (empty($unitLayanan)): ?>
                    <div class="alert alert-info text-center" role="alert">
                        <?= __lang('no_active_services') ?>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($unitLayanan as $unit): ?>
                            <div class="col-md-6 col-lg-4">
                                <article class="unit-card">
                                    <h3><?= esc($unit['nama_unit']) ?></h3>
                                    <p><?= esc($unit['deskripsi'] ?? '') ?></p>
                                    <a href="<?= base_url('survei/' . $unit['id']) ?>" 
                                       class="btn-survei"
                                       aria-label="<?= __lang('fill_survey_for') ?> <?= esc($unit['nama_unit']) ?>">
                                        <i class="fas fa-poll me-2"></i><?= __lang('fill_survey') ?>
                                    </a>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Dashboard Transparency Link -->
                <div class="dashboard-link">
                    <h2><?= __lang('transparency_dashboard') ?></h2>
                    <p class="mb-4"><?= __lang('view_ikm_results') ?></p>
                    <a href="<?= base_url('dashboard') ?>" class="btn-dashboard">
                        <i class="fas fa-chart-bar me-2"></i><?= __lang('go_to_dashboard') ?>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="site-footer" role="contentinfo">
        <div class="container">
            <div class="row">
                <div class="col-md-4 footer-content">
                    <h4><?= esc($instansi['nama']) ?></h4>
                    <p><?= esc($instansi['alamat'] ?? '') ?></p>
                    <p><i class="fas fa-phone me-2"></i><?= esc($instansi['telepon'] ?? '') ?></p>
                    <p><i class="fas fa-envelope me-2"></i><?= esc($instansi['email'] ?? '') ?></p>
                </div>
                
                <div class="col-md-4 footer-content">
                    <h4><?= __lang('quick_links') ?></h4>
                    <ul>
                        <li><a href="<?= base_url() ?>"><?= __lang('home') ?></a></li>
                        <li><a href="<?= base_url('dashboard') ?>"><?= __lang('dashboard') ?></a></li>
                        <li><a href="<?= base_url('survei') ?>"><?= __lang('surveys') ?></a></li>
                    </ul>
                </div>
                
                <div class="col-md-4 footer-content">
                    <h4><?= __lang('accessibility') ?></h4>
                    <ul>
                        <li><a href="#main-content"><?= __lang('skip_to_content') ?></a></li>
                        <li><a href="<?= base_url('accessibility') ?>"><?= __lang('accessibility_statement') ?></a></li>
                        <li><a href="<?= base_url('privacy') ?>"><?= __lang('privacy_policy') ?></a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?= date('Y') ?> <?= esc($instansi['nama']) ?>. <?= __lang('all_rights_reserved') ?></p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Additional Scripts -->
    <script>
        // Preload critical resources
        window.addEventListener('DOMContentLoaded', function() {
            // Lazy load non-critical images if any
            const images = document.querySelectorAll('img[data-src]');
            images.forEach(img => {
                img.src = img.dataset.src;
            });
        });
    </script>
</body>
</html>
