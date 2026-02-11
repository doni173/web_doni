 <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service | Sistem Inventory dan Kasir</title>

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

<h2>Data Service</h2>

<!-- ================= SEARCH & ACTION ================= -->
<div class="buttons">
    <form action="{{ route('services.index') }}" method="GET" class="search-form" id="searchForm">
        <div class="search-input-wrapper">
            <input type="text"
                   class="form-control"
                   placeholder="Cari service..."
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

        <button type="button" class="btn-add" data-toggle="modal" data-target="#addServiceModal">
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
    <th>ID Service</th>
    <th>Nama Service</th>
    <th>Harga Jual</th>
    <th>Diskon</th>
    <th>Harga Setelah Diskon</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody id="serviceTableBody">

@forelse($services as $service)
<tr>
    <td data-label="ID Service">
        <span class="id-badge">{{ $service->id_service }}</span>
    </td>
    <td data-label="Nama Service">
        <span class="product-name">{{ $service->service }}</span>
    </td>
    <td data-label="Harga Jual">Rp {{ number_format($service->harga_jual,0,',','.') }}</td>
    <td data-label="Diskon">{{ $service->diskon }}%</td>
    <td data-label="Harga Setelah Diskon">Rp {{ number_format($service->harga_setelah_diskon,0,',','.') }}</td>
    <td data-label="Aksi">
        <div class="action-buttons">
            <button class="btn-edit"
                    data-toggle="modal"
                    data-target="#editServiceModal{{ $service->id_service }}"
                    title="Edit">
                <i class="fas fa-edit"></i>
            </button>

            <button class="btn-delete"
                    onclick="confirmDelete('{{ $service->id_service }}')"
                    title="Hapus">
                <i class="fas fa-trash-alt"></i>
            </button>

            <form id="delete-form-{{ $service->id_service }}"
                  action="{{ route('services.destroy', $service->id_service) }}"
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
    <td colspan="6" class="text-center">
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Tidak ada data service</p>
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
<div class="modal fade" id="addServiceModal" tabindex="-1" role="dialog" aria-labelledby="addServiceModalLabel" aria-hidden="true">
<div class="modal-dialog" role="document">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title" id="addServiceModalLabel">
        <i class="bi bi-plus-circle"></i>
        Tambah Service
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body">
<form action="{{ route('services.store') }}" method="POST">
@csrf

<div class="form-group">
    <label for="service">Nama Service</label>
    <input type="text" class="form-control" id="service" name="service" placeholder="Masukkan nama service" required>
</div>

<div class="form-group">
    <label for="harga_jual">Harga Jual (Rp)</label>
    <input type="text" class="form-control rupiah" id="harga_jual" name="harga_jual" placeholder="Masukkan harga jual" autocomplete="off" required>
</div>

<div class="form-group">
    <label for="diskon">Diskon (%)</label>
    <input type="number" class="form-control diskon" id="diskon" name="diskon" min="0" max="100" step="1" value="0" placeholder="Masukkan diskon" required>
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
@foreach($services as $service)
<div class="modal fade" id="editServiceModal{{ $service->id_service }}" tabindex="-1" role="dialog" aria-labelledby="editServiceModalLabel{{ $service->id_service }}" aria-hidden="true">
<div class="modal-dialog" role="document">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title" id="editServiceModalLabel{{ $service->id_service }}">
        <i class="bi bi-pencil-square"></i>
        Edit Service
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body">
<form action="{{ route('services.update', $service->id_service) }}" method="POST">
@csrf
@method('PUT')

<div class="form-group">
    <label for="service{{ $service->id_service }}">Nama Service</label>
    <input type="text"
           class="form-control"
           id="service{{ $service->id_service }}"
           name="service"
           value="{{ $service->service }}"
           required>
</div>

<div class="form-group">
    <label for="harga_jual{{ $service->id_service }}">Harga Jual (Rp)</label>
    <input type="text"
           class="form-control rupiah"
           id="harga_jual{{ $service->id_service }}"
           name="harga_jual"
           value="{{ number_format($service->harga_jual,0,',','.') }}"
           autocomplete="off"
           required>
</div>

<div class="form-group">
    <label for="diskon{{ $service->id_service }}">Diskon (%)</label>
    <input type="number"
           class="form-control diskon"
           id="diskon{{ $service->id_service }}"
           name="diskon"
           value="{{ $service->diskon }}"
           min="0"
           max="100"
           step="1"
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
        text: 'Data service ini akan dihapus permanen!',
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
    const tableBody = $('#serviceTableBody');

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
            url: '{{ route("services.index") }}',
            type: 'GET',
            data: { q: query },
            success: function(response) {
                // Parse HTML response
                const parser = new DOMParser();
                const doc = parser.parseFromString(response, 'text/html');
                const newTableBody = doc.querySelector('#serviceTableBody');
                
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

    // ================= FORMAT RUPIAH ================= 
    $(document).on('input', '.rupiah', function() {
        let val = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(val.replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
    });

    // Remove formatting sebelum submit
    $('form').on('submit', function() {
        $(this).find('.rupiah').each(function() {
            $(this).val($(this).val().replace(/\./g, ''));
        });
    });

    // ================= DISKON INTEGER ================= 
    $(document).on('input', '.diskon', function() {
        let val = $(this).val().replace(/[^0-9]/g, '');
        if (parseInt(val) > 100) val = 100;
        $(this).val(val);
    });
});
</script>

</body>
</html>