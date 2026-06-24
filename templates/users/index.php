<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna</title>
    
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <!-- Custom CSS -->
    <style>
        .badge-purple {
            background-color: #6f42c1;
            color: white;
        }
        .table-actions {
            white-space: nowrap;
        }
        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-users"></i> Manajemen Pengguna</h4>
                        <button class="btn btn-primary" id="btnAddUser">
                            <i class="fas fa-plus"></i> Tambah User
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Filter Section -->
                        <div class="filter-section">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="filterRole">Filter Role:</label>
                                    <select class="form-control" id="filterRole">
                                        <option value="">Semua Role</option>
                                        <option value="super_admin">Super Admin</option>
                                        <option value="admin">Admin</option>
                                        <option value="operator">Operator</option>
                                        <option value="pimpinan">Pimpinan</option>
                                        <option value="dpo">DPO</option>
                                        <option value="devops">DevOps</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filterStatus">Filter Status:</label>
                                    <select class="form-control" id="filterStatus">
                                        <option value="">Semua Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-info btn-block" id="btnApplyFilter">
                                        <i class="fas fa-filter"></i> Terapkan Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Users Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Nama Lengkap</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        // Initialize DataTable with server-side processing
        var table = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/api/users/data',
                type: 'GET',
                data: function(d) {
                    d.role = $('#filterRole').val();
                    d.status = $('#filterStatus').val();
                }
            },
            columns: [
                { data: 'id', width: '5%' },
                { data: 'username' },
                { data: 'email' },
                { data: 'full_name' },
                { data: 'role_badge' },
                { data: 'status_badge' },
                { data: 'created_at_formatted' },
                { 
                    data: 'actions',
                    width: '15%',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[0, 'desc']],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            responsive: true,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
                emptyTable: 'Tidak ada data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data yang ditampilkan',
                infoFiltered: '(difilter dari _MAX_ total data)',
                search: 'Cari:',
                paginate: {
                    first: '<<',
                    last: '>>',
                    next: '>',
                    previous: '<'
                }
            }
        });
        
        // Apply filter button
        $('#btnApplyFilter').click(function() {
            table.ajax.reload();
        });
        
        // Add user button
        $('#btnAddUser').click(function() {
            window.location.href = '/users/create';
        });
        
        // View user detail
        $(document).on('click', '.view-btn', function() {
            var userId = $(this).data('id');
            window.location.href = '/users/' + userId;
        });
        
        // Edit user
        $(document).on('click', '.edit-btn', function() {
            var userId = $(this).data('id');
            window.location.href = '/users/' + userId + '/edit';
        });
        
        // Reset password
        $(document).on('click', '.reset-password-btn', function() {
            var userId = $(this).data('id');
            
            Swal.fire({
                title: 'Reset Password',
                html: `
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" id="newPassword" class="form-control" placeholder="Minimal 8 karakter">
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <input type="password" id="confirmPassword" class="form-control" placeholder="Ulangi password baru">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Reset Password',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#d33',
                preConfirm: () => {
                    const newPassword = document.getElementById('newPassword').value;
                    const confirmPassword = document.getElementById('confirmPassword').value;
                    
                    if (!newPassword || newPassword.length < 8) {
                        Swal.showValidationMessage('Password minimal 8 karakter');
                        return false;
                    }
                    
                    if (newPassword !== confirmPassword) {
                        Swal.showValidationMessage('Konfirmasi password tidak cocok');
                        return false;
                    }
                    
                    return fetch('/api/users/' + userId + '/reset-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            'new_password': newPassword,
                            'confirm_password': confirmPassword
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Gagal mereset password');
                        }
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(error.message);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire(
                        'Berhasil!',
                        'Password berhasil direset',
                        'success'
                    ).then(() => {
                        table.ajax.reload(null, false);
                    });
                }
            });
        });
        
        // Activate user
        $(document).on('click', '.activate-btn', function() {
            var userId = $(this).data('id');
            
            Swal.fire({
                title: 'Aktifkan User?',
                text: 'User ini akan dapat login kembali.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Aktifkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/api/users/' + userId + '/activate', {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', 'User berhasil diaktifkan', 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Gagal!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error!', 'Terjadi kesalahan: ' + error.message, 'error');
                    });
                }
            });
        });
        
        // Deactivate user
        $(document).on('click', '.deactivate-btn', function() {
            var userId = $(this).data('id');
            
            Swal.fire({
                title: 'Nonaktifkan User?',
                text: 'User ini tidak akan dapat login lagi.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Nonaktifkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/api/users/' + userId + '/deactivate', {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', 'User berhasil dinonaktifkan', 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Gagal!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error!', 'Terjadi kesalahan: ' + error.message, 'error');
                    });
                }
            });
        });
        
        // Delete user
        $(document).on('click', '.delete-btn', function() {
            var userId = $(this).data('id');
            
            Swal.fire({
                title: 'Hapus User?',
                text: 'User akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan!',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/api/users/' + userId, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', 'User berhasil dihapus', 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Gagal!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error!', 'Terjadi kesalahan: ' + error.message, 'error');
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
