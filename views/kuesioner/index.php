<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Kuesioner IKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Manajemen Kuesioner IKM</h5>
            <?php if(in_array($_SESSION['user']['role'], ['Super Admin', 'Admin'])): ?>
                <a href="/kuesioner/preview" class="btn btn-light btn-sm" target="_blank">
                    <i class="fas fa-eye me-1"></i> Preview Kuesioner
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <!-- Info Box -->
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>9 Unsur Wajib IKM:</strong> Sistem ini menggunakan 9 unsur wajib sesuai Peraturan Menteri PANRB. 
                Unsur U1-U9 tidak dapat dihapus, namun teks pertanyaan dapat disesuaikan untuk kebutuhan unit layanan Anda.
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table id="kuesionerTable" class="table table-striped table-hover" style="width: 100%">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Kode</th>
                            <th>Nama Unsur / Pertanyaan</th>
                            <th>Deskripsi</th>
                            <th width="8%">Bobot</th>
                            <th width="10%">Status</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- JS Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    var table = $('#kuesionerTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/kuesioner/data',
        columns: [
            { 
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'unsur_code' },
            { 
                data: 'nama_unsur',
                render: function(data, type, row) {
                    let badge = row.unsur_code.match(/^U[1-9]$/) ? '<span class="badge bg-warning text-dark me-1">Wajib</span>' : '';
                    return badge + data;
                }
            },
            { 
                data: 'deskripsi',
                render: function(data) {
                    return data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : '-';
                }
            },
            { data: 'bobot' },
            { 
                data: 'is_active',
                render: function(data, type, row) {
                    let badge = data == 1 ? 'bg-success' : 'bg-secondary';
                    let status = data == 1 ? 'Aktif' : 'Nonaktif';
                    return `<span class="badge ${badge}">${status}</span>`;
                }
            },
            {
                data: 'id_kuesioner',
                render: function(data, type, row) {
                    let actions = `
                        <a href="/kuesioner/${data}" class="btn btn-info btn-sm text-white" title="Lihat Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="/kuesioner/${data}/edit" class="btn btn-warning btn-sm text-white" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="toggleStatus(${data}, ${row.is_active})" class="btn btn-${row.is_active == 1 ? 'secondary' : 'success'} btn-sm" title="${row.is_active == 1 ? 'Nonaktifkan' : 'Aktifkan'}">
                            <i class="fas fa-${row.is_active == 1 ? 'pause' : 'play'}"></i>
                        </button>
                    `;
                    
                    // Delete button only for non-wajib elements
                    if (!row.unsur_code.match(/^U[1-9]$/)) {
                        actions += `
                            <button onclick="deleteKuesioner(${data}, '${row.nama_unsur}')" class="btn btn-danger btn-sm" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                    }
                    
                    return actions;
                }
            }
        ],
        order: [[2, 'asc']]
    });
});

function toggleStatus(id, currentStatus) {
    Swal.fire({
        title: currentStatus == 1 ? 'Nonaktifkan Kuesioner?' : 'Aktifkan Kuesioner?',
        text: currentStatus == 1 
            ? 'Kuesioner ini tidak akan ditampilkan kepada responden.' 
            : 'Kuesioner ini akan ditampilkan kepada responden.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Lanjutkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/kuesioner/${id}/toggle-status`,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#kuesionerTable').DataTable().ajax.reload();
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let msg = 'Terjadi kesalahan pada server.';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', msg, 'error');
                }
            });
        }
    });
}

function deleteKuesioner(id, name) {
    Swal.fire({
        title: 'Hapus Kuesioner?',
        text: `Apakah Anda yakin ingin menghapus "${name}"? Tindakan ini tidak dapat dibatalkan.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/kuesioner/${id}`,
                type: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire('Terhapus!', response.message, 'success');
                        $('#kuesionerTable').DataTable().ajax.reload();
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let msg = 'Terjadi kesalahan pada server.';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', msg, 'error');
                }
            });
        }
    });
}
</script>
</body>
</html>
