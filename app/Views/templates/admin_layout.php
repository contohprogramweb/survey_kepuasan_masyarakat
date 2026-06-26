<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Dashboard Admin IKM</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.b.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- AOS Animation CSS -->
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-bg: #f8f9fc;
            --dark-text: #5a5c69;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            color: white;
            transition: all 0.3s ease;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem 1.5rem;
            margin: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.15);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.2);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            font-size: 1.5rem;
            font-weight: 800;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            animation: pulse 3s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        /* Content Wrapper */
        .content-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem;
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Card Styles with Hover Effects */
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.175);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 2px solid #e3e6f0;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        /* Border Left Utilities */
        .border-left-primary { border-left: 0.35rem solid var(--primary-color) !important; }
        .border-left-success { border-left: 0.35rem solid var(--success-color) !important; }
        .border-left-info { border-left: 0.35rem solid var(--info-color) !important; }
        .border-left-warning { border-left: 0.35rem solid var(--warning-color) !important; }
        .border-left-danger { border-left: 0.35rem solid var(--danger-color) !important; }
        
        /* Button Styles */
        .btn {
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn:hover::after {
            width: 300px;
            height: 300px;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .btn:active {
            transform: translateY(-1px);
        }
        
        /* Topbar */
        .topbar {
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            backdrop-filter: blur(10px);
        }
        
        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 0.65rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-5px); }
            60% { transform: translateY(-3px); }
        }
        
        /* Table Styling */
        .table-responsive {
            border-radius: 0.75rem;
            overflow: hidden;
        }
        
        table.dataTable thead th {
            background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
            border-bottom: 3px solid var(--primary-color);
            font-weight: 700;
            color: var(--primary-color);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        table.dataTable tbody tr {
            transition: all 0.2s ease;
        }
        
        table.dataTable tbody tr:hover {
            background-color: #f1f4f9;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        /* Utility Classes */
        .text-gradient {
            background: linear-gradient(45deg, var(--primary-color), var(--info-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .shadow-hover:hover {
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important;
        }
        
        .loading-spinner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        .loading-spinner.show {
            display: flex;
        }
        
        /* Dropdown Menu */
        .dropdown-menu {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 2rem rgba(0,0,0,0.15);
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dropdown-item {
            padding: 0.75rem 1.5rem;
            transition: all 0.2s ease;
            border-radius: 0.5rem;
            margin: 0.25rem 0.5rem;
        }
        
        .dropdown-item:hover {
            background-color: var(--light-bg);
            transform: translateX(5px);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .sidebar {
                min-width: 250px;
            }
        }
    </style>
    
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loading-spinner">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 fw-bold text-primary">Memuat data...</p>
    </div>

    <div class="content-wrapper">
        <!-- Topbar -->
        <nav class="navbar navbar-expand-lg topbar mb-4 static-top">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold text-gradient" href="<?= site_url('admin/dashboard') ?>">
                    <i class="fas fa-chart-line me-2"></i>Dashboard IKM
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <!-- Notifications -->
                        <li class="nav-item dropdown position-relative">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell fa-lg"></i>
                                <span class="badge bg-danger notification-badge" id="notification-badge">0</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                                <li><h6 class="dropdown-header fw-bold"><i class="fas fa-bell me-2"></i>Notifikasi</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <div id="notification-list">
                                    <li><a class="dropdown-item text-center text-muted py-4" href="#"><i class="fas fa-info-circle me-2"></i>Tidak ada notifikasi baru</a></li>
                                </div>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center small fw-bold" href="<?= site_url('notifications') ?>"><i class="fas fa-list me-2"></i>Lihat semua notifikasi</a></li>
                            </ul>
                        </li>
                        
                        <!-- User Dropdown -->
                        <li class="nav-item dropdown ms-3">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="me-2 d-none d-lg-inline text-gray-700 fw-semibold"><?= session()->get('username') ?? 'Admin' ?></span>
                                <img class="img-profile rounded-circle shadow-sm" src="https://ui-avatars.com/api/?name=<?= urlencode(session()->get('username') ?? 'Admin') ?>&background=4e73df&color=fff&size=128" width="40" height="40" alt="Profile">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><h6 class="dropdown-header fw-bold">Menu Pengguna</h6></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user fa-sm fa-fw me-2 text-primary"></i> Profil Saya</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cogs fa-sm fa-fw me-2 text-warning"></i> Pengaturan</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-history fa-sm fa-fw me-2 text-info"></i> Aktivitas</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger fw-bold" href="<?= site_url('auth/logout') ?>"><i class="fas fa-sign-out-alt fa-sm fa-fw me-2"></i> Logout</a></li>
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
        <footer class="sticky-footer bg-white py-4 mt-auto shadow-sm">
            <div class="container my-auto">
                <div class="copyright text-center">
                    <span class="text-gray-600">&copy; <?= date('Y') ?> <strong class="text-primary">Sistem IKM v2.0.0</strong>. All Rights Reserved.</span>
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <!-- Initialization Scripts -->
    <script>
    // Initialize AOS Animation
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        offset: 50
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips and popovers
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        popoverTriggerList.forEach(popoverTriggerEl => {
            new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Load notifications
        loadNotifications();
        
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
    
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
    
    // Show loading spinner
    function showLoading(message = 'Memuat data...') {
        document.querySelector('#loading-spinner p').textContent = message;
        document.getElementById('loading-spinner').classList.add('show');
    }
    
    // Hide loading spinner
    function hideLoading() {
        document.getElementById('loading-spinner').classList.remove('show');
    }
    
    // SweetAlert2 confirmation helper
    function confirmAction(title, message, confirmCallback) {
        Swal.fire({
            title: title,
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4e73df',
            cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-check me-2"></i>Ya, Lanjutkan!',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed && confirmCallback) {
                confirmCallback();
            }
        });
    }
    
    // Success toast helper
    function showSuccessToast(message, title = 'Berhasil!') {
        Swal.fire({
            icon: 'success',
            title: title,
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
    
    // Error toast helper
    function showErrorToast(message, title = 'Terjadi Kesalahan!') {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });
    }
    </script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>
