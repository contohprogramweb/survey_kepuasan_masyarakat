<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Periode Survei</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Periode Survei</h5>
            <div>
                <?php if(in_array($_SESSION['user']['role'], ['Super Admin', 'Admin'])): ?>
                    <button onclick="runScheduler()" class="btn btn-warning btn-sm me-2 text-dark" title="Jalankan Update Status">
                        <i class="fas fa-sync"></i> Sync Status
                    </button>
                    <a href="/periods/create" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i> Tambah Periode
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <table id="periodeTable" class="table table-striped table-hover" style="width: 100%">
                <thead>
                    <tr>
                        <th>Unit</th>
                        <th>Nama Periode</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Status DB</th>
                        <th>Status Realtime</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#periodeTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/periods/data',
        columns: [
            { data: 'unit_nama' },
            { data: 'nama_periode' },
            { data: 'tanggal_mulai' },
            { data: 'tanggal_selesai' },
            { 
                data: 'status',
                render: function(data) {
                    let color = data === 'aktif' ? 'bg-success' : (data === 'selesai' ? 'bg-secondary' : 'bg-info');
                    return `<span class="badge ${color}">${data}</span>`;
                }
            },
            { 
                data: 'status_display',
                render: function(data) {
                    let color = data === 'aktif' ? 'bg-success' : (data === 'selesai' ? 'bg-secondary' : 'bg-warning text-dark');
                    return `<span class="badge ${color}">${data}</span>`;
                }
            },
            {
                data: 'id',
                render: function(data, type, row) {
                    let actions = `<a href="/periods/${data}" class="btn btn-info btn-sm text-white"><i class="fas fa-eye"></i></a> `;
                    <?php if(in_array($_SESSION['user']['role'], ['Super Admin', 'Admin'])): ?>
                    actions += `<a href="/periods/${data}/edit" class="btn btn-warning btn-sm text-white"><i class="fas fa-edit"></i></a> `;
                    actions += `<button onclick="deletePeriode(${data})" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>`;
                    <?php endif; ?>
                    return actions;
                }
            }
        ]
    });
});

function deletePeriode(id) {
    Swal.fire({
        title: 'Hapus Periode?',
        text: "Data survei terkait tidak akan terhapus, tapi periode ini akan hilang dari daftar.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/periods/${id}`,
                type: 'DELETE',
                success: function(res) {
                    Swal.fire('Terhapus!', '', 'success');
                    $('#periodeTable').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Gagal menghapus.';
                    Swal.fire('Error!', msg, 'error');
                }
            });
        }
    });
}

function runScheduler() {
    Swal.fire({ title: 'Menjalankan Scheduler...', didOpen: () => Swal.showLoading() });
    $.get('/periods/scheduler', function(res) {
        Swal.fire('Selesai', res.message, 'success');
        $('#periodeTable').DataTable().ajax.reload();
    });
}
</script>
</body>
</html>
