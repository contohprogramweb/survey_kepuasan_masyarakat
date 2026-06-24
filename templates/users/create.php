<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User Baru</title>
    
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-user-plus"></i> Tambah User Baru</h4>
                    </div>
                    <div class="card-body">
                        <form id="createUserForm">
                            <div class="form-group">
                                <label for="username">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" required minlength="3">
                                <small class="form-text text-muted">Minimal 3 karakter, unik</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <small class="form-text text-muted">Harus unik dan format email valid</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="full_name">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                <small class="form-text text-muted">Minimal 8 karakter</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="password_confirm">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Role <span class="text-danger">*</span></label>
                                <select class="form-control" id="role" name="role" required>
                                    <?php foreach ($roles as $value => $label): ?>
                                    <option value="<?php echo htmlspecialchars($value); ?>">
                                        <?php echo htmlspecialchars($label); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <?php foreach ($statuses as $value => $label): ?>
                                    <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $value === 'active' ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($label); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="mfa_enabled" name="mfa_enabled">
                                <label class="form-check-label" for="mfa_enabled">Aktifkan MFA (Multi-Factor Authentication)</label>
                            </div>
                            
                            <hr>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan User
                                </button>
                                <a href="/users" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        $('#createUserForm').submit(function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            
            $.ajax({
                url: '/api/users',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'User berhasil ditambahkan',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            window.location.href = '/users';
                        });
                    } else {
                        var errorMessage = response.message || 'Gagal menambahkan user';
                        if (response.errors && response.errors.length > 0) {
                            errorMessage += '<ul>' + response.errors.map(function(err) {
                                return '<li>' + err + '</li>';
                            }).join('') + '</ul>';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            html: errorMessage
                        });
                    }
                },
                error: function(xhr) {
                    var message = 'Terjadi kesalahan pada server';
                    try {
                        var response = xhr.responseJSON;
                        if (response && response.message) {
                            message = response.message;
                        }
                    } catch (e) {}
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: message
                    });
                }
            });
        });
        
        // Password confirmation validation
        $('#password_confirm').on('input', function() {
            var password = $('#password').val();
            var confirmPassword = $(this).val();
            
            if (confirmPassword && password !== confirmPassword) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
    });
    </script>
</body>
</html>
