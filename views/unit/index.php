<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Unit Layanan</title>
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Manajemen Unit Layanan</h5>
            <?php if(in_array($_SESSION['user']['role'], ['Super Admin', 'Admin'])): ?>
                <a href="/units/create" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i> Tambah Unit
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table id="unitsTable" class="table table-striped table-hover" style="width: 100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kode</th>
                            <th>Nama Unit</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th width="150">Aksi</th>
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
    var table = $('#unitsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/units/data',
            type: 'GET',
            data: function(d) {
                d.filters = {
                    status: $('#filterStatus').val()
                };
            }
        },
        columns: [
            { data: 'id', width: '5%' },
            { data: 'kode_unit' },
            { data: 'nama_unit' },
            { 
                data: 'status',
                render: function(data) {
                    var badge = data === 'aktif' ? 'bg-success' : 'bg-secondary';
                    return `<span class="badge ${badge}">${data}</span>`;
                }
            },
            { data: 'created_at' },
            {
                data: 'id',
                render: function(data, type, row) {
                    let actions = `<a href="/units/${data}" class="btn btn-info btn-sm text-white"><i class="fas fa-eye"></i></a> `;
                    <?php if(in_array($_SESSION['user']['role'], ['Super Admin', 'Admin'])): ?>
                    actions += `<a href="/units/${data}/edit" class="btn btn-warning btn-sm text-white"><i class="fas fa-edit"></i></a> `;
                    actions += `<button onclick="deleteUnit(${data}, '${row.nama_unit}')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>`;
                    <?php endif; ?>
                    return actions;
                }
            }
        ],
        order: [[1, 'asc']]
    });

    // Filter Event
    $('#filterStatus').on('change', function() {
        table.ajax.reload();
    });
});

function deleteUnit(id, name) {
    Swal.fire({
        title: 'Hapus Unit?',
        text: `Apakah Anda yakin ingin menghapus unit "${name}"? Tindakan ini tidak dapat dibatalkan sepenuhnya (soft delete).`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/units/${id}`,
                type: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire('Terhapus!', response.message, 'success');
                        $('#unitsTable').DataTable().ajax.reload();
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
