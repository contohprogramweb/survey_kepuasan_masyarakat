<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Dashboard Admin IKM</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-bg: #f8f9fc;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--light-bg);
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        
        .content-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .border-left-primary { border-left: 0.25rem solid var(--primary-color) !important; }
        .border-left-success { border-left: 0.25rem solid var(--success-color) !important; }
        .border-left-info { border-left: 0.25rem solid var(--info-color) !important; }
        .border-left-warning { border-left: 0.25rem solid var(--warning-color) !important; }
        .border-left-danger { border-left: 0.25rem solid var(--danger-color) !important; }
        
        .text-gray-800 { color: #5a5c69 !important; }
        .text-gray-300 { color: #dddfeb !important; }
        
        .topbar {
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 0.6rem;
        }
    </style>
    
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <div class="content-wrapper">
        <!-- Topbar -->
        <nav class="navbar navbar-expand-lg topbar mb-4 static-top">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold text-primary" href="<?= site_url('admin/dashboard') ?>">
                    <i class="fas fa-chart-line me-2"></i>Dashboard IKM
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <!-- Notifications -->
                        <li class="nav-item dropdown position-relative">
                            <a class="nav-link" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell fa-lg"></i>
                                <span class="badge bg-danger notification-badge" id="notification-badge">0</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="width: 300px;">
                                <li><h6 class="dropdown-header">Notifikasi</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <div id="notification-list">
                                    <li><a class="dropdown-item text-center text-muted" href="#">Tidak ada notifikasi baru</a></li>
                                </div>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center small" href="<?= site_url('notifications') ?>">Lihat semua</a></li>
                            </ul>
                        </li>
                        
                        <!-- User Dropdown -->
                        <li class="nav-item dropdown ms-3">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <span class="me-2 d-none d-lg-inline text-gray-600 small"><?= session()->get('username') ?? 'Admin' ?></span>
                                <img class="img-profile rounded-circle" src="https://ui-avatars.com/api/?name=<?= session()->get('username') ?? 'Admin' ?>&background=4e73df&color=fff" width="32" height="32">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user fa-sm fa-fw me-2"></i> Profil</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cogs fa-sm fa-fw me-2"></i> Pengaturan</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= site_url('auth/logout') ?>"><i class="fas fa-sign-out-alt fa-sm fa-fw me-2"></i> Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <?= $this->renderSection('content') ?>
        </main>

        <!-- Footer -->
        <footer class="sticky-footer bg-white py-3 mt-auto">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; <?= date('Y') ?> Sistem IKM v2.0.0</span>
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Notification Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load notifications
        loadNotifications();
        
        function loadNotifications() {
            fetch('<?= site_url('api/notifications/count') ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.unread_count > 0) {
                        document.getElementById('notification-badge').textContent = data.unread_count;
                        document.getElementById('notification-badge').style.display = 'inline-block';
                    }
                })
                .catch(err => console.error('Error loading notifications:', err));
        }
    });
    </script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>
