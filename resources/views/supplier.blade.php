<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier | Sistem Inventory dan Kasir</title>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

@include('layouts.navbar')
@include('layouts.sidebar')
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="main-container">
<div class="main-content">

<h2>Data Supplier</h2>

<!-- ================= SEARCH & ACTION ================= -->
<div class="buttons">
    <form action="{{ route('suppliers.index') }}" method="GET" class="search-form" id="searchForm">
        <div class="search-input-wrapper">
            <input type="text"
                   class="form-control"
                   placeholder="Cari supplier..."
                   name="q"
                   value="{{ request('q') }}"
                   id="searchInput"
                   autocomplete="off">
            <span class="search-icon">
                <i class="bi bi-arrow-clockwise spin" id="searchLoading" style="display: none;"></i>
            </span>
        </div>

        <button type="button" class="btn-clear" id="clearSearch" style="{{ request('q') ? '' : 'display: none;' }}">
            <i class="bi bi-x-circle"></i>
            <span>Clear</span>
        </button>

        <button type="button" class="btn-add" data-toggle="modal" data-target="#addSupplierModal">
            <i class="bi bi-plus-circle"></i>
            <span>Tambah Data</span>
        </button>
    </form>

    <div class="search-info" id="searchInfo" style="display: none;">
        Menampilkan hasil pencarian untuk: <strong id="searchTerm"></strong>
    </div>
</div>

<!-- ================= TABLE ================= -->
<div class="table-responsive">
<table class="table-main">
<thead>
<tr>
    <th>ID Supplier</th>
    <th>Nama Supplier</th>
    <th>No HP</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody id="supplierTableBody">

@forelse($suppliers as $supplier)
<tr>
    <td data-label="ID Supplier">
        <span class="id-badge">{{ $supplier->id_supplier }}</span>
    </td>
    <td data-label="Nama Supplier">
        <span class="product-name">{{ $supplier->nama_supplier }}</span>
    </td>
    <td data-label="No HP">{{ $supplier->no_hp }}</td>
    <td data-label="Aksi">
        <div class="action-buttons">
            <button class="btn-edit"
                    data-toggle="modal"
                    data-target="#editSupplierModal{{ $supplier->id_supplier }}"
                    title="Edit">
                <i class="fas fa-edit"></i>
            </button>

            <button class="btn-delete"
                    onclick="confirmDelete('{{ $supplier->id_supplier }}')"
                    title="Hapus">
                <i class="fas fa-trash-alt"></i>
            </button>

            <form id="delete-form-{{ $supplier->id_supplier }}"
                  action="{{ route('suppliers.destroy', $supplier->id_supplier) }}"
                  method="POST"
                  style="display:none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="4" class="text-center">
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Tidak ada data supplier</p>
        </div>
    </td>
</tr>
@endforelse

</tbody>
</table>
</div>

</div>
</div>

<!-- ================= MODAL TAMBAH ================= -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
<div class="modal-dialog" role="document">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title" id="addSupplierModalLabel">
        <i class="bi bi-plus-circle"></i>
        Tambah Supplier
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body">
<form action="{{ route('suppliers.store') }}" method="POST">
@csrf

<div class="form-group">
    <label for="nama_supplier">Nama Supplier</label>
    <input type="text" class="form-control" id="nama_supplier" name="nama_supplier" placeholder="Masukkan nama supplier" required>
</div>

<div class="form-group">
    <label for="no_hp">No HP</label>
    <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="Masukkan no HP" required>
</div>

<div class="modal-footer-custom">
    <button type="button" class="btn-secondary" data-dismiss="modal">Batal</button>
    <button type="submit" class="btn-success">
        <i class="bi bi-check-circle"></i> Simpan
    </button>
</div>

</form>
</div>
</div>
</div>
</div>

<!-- ================= MODAL EDIT ================= -->
@foreach($suppliers as $supplier)
<div class="modal fade" id="editSupplierModal{{ $supplier->id_supplier }}" tabindex="-1" role="dialog" aria-labelledby="editSupplierModalLabel{{ $supplier->id_supplier }}" aria-hidden="true">
<div class="modal-dialog" role="document">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title" id="editSupplierModalLabel{{ $supplier->id_supplier }}">
        <i class="bi bi-pencil-square"></i>
        Edit Supplier
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body">
<form action="{{ route('suppliers.update', $supplier->id_supplier) }}" method="POST">
@csrf
@method('PUT')

<div class="form-group">
    <label for="nama_supplier{{ $supplier->id_supplier }}">Nama Supplier</label>
    <input type="text"
           class="form-control"
           id="nama_supplier{{ $supplier->id_supplier }}"
           name="nama_supplier"
           value="{{ $supplier->nama_supplier }}"
           required>
</div>

<div class="form-group">
    <label for="no_hp{{ $supplier->id_supplier }}">No HP</label>
    <input type="text"
           class="form-control"
           id="no_hp{{ $supplier->id_supplier }}"
           name="no_hp"
           value="{{ $supplier->no_hp }}"
           required>
</div>

<div class="modal-footer-custom">
    <button type="button" class="btn-secondary" data-dismiss="modal">Batal</button>
    <button type="submit" class="btn-success">
        <i class="bi bi-check-circle"></i> Update
    </button>
</div>

</form>
</div>
</div>
</div>
</div>
@endforeach

<!-- ================= SWEETALERT ================= -->
@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: '{{ session('success') }}',
    timer: 2000,
    showConfirmButton: false
});
</script>
@endif

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('active');
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: 'Data supplier ini akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}

// ================= LIVE SEARCH FUNCTIONALITY ================= 
$(document).ready(function() {
    let searchTimeout;
    const searchInput = $('#searchInput');
    const searchLoading = $('#searchLoading');
    const searchIcon = $('#searchIcon');
    const clearButton = $('#clearSearch');
    const searchInfo = $('#searchInfo');
    const searchTerm = $('#searchTerm');
    const tableBody = $('#supplierTableBody');

    // Live search saat user mengetik
    searchInput.on('input', function() {
        const query = $(this).val().trim();
        clearTimeout(searchTimeout);

        if (query.length > 0) {
            clearButton.show();
        } else {
            clearButton.hide();
            searchInfo.hide();
        }

        // Delay 300ms sebelum search untuk mengurangi request
        searchTimeout = setTimeout(function() {
            performSearch(query);
        }, 300);
    });

    // Clear button click
    clearButton.on('click', function() {
        searchInput.val('');
        clearButton.hide();
        searchInfo.hide();
        performSearch('');
    });

    // Fungsi untuk melakukan search via AJAX
    function performSearch(query) {
        searchIcon.hide();
        searchLoading.show();

        $.ajax({
            url: '{{ route("suppliers.index") }}',
            type: 'GET',
            data: { q: query },
            success: function(response) {
                // Parse HTML response
                const parser = new DOMParser();
                const doc = parser.parseFromString(response, 'text/html');
                const newTableBody = doc.querySelector('#supplierTableBody');
                
                if (newTableBody) {
                    tableBody.html(newTableBody.innerHTML);
                }

                // Tampilkan search info jika ada query
                if (query.length > 0) {
                    searchTerm.text(query);
                    searchInfo.show();
                } else {
                    searchInfo.hide();
                }

                searchLoading.hide();
                searchIcon.show();
            },
            error: function(xhr, status, error) {
                console.error('Search error:', error);
                searchLoading.hide();
                searchIcon.show();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan saat mencari data!',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    }

    // Prevent form submit
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        return false;
    });
});
</script>

</body>
</html>