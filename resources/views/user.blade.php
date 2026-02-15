<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengguna | Sistem Inventory dan Kasir</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Pagination Styles */
        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 15px 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .pagination-info {
            color: #6b7280;
            font-size: 14px;
        }

        .pagination-controls {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #374151;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            min-width: 36px;
            text-align: center;
        }

        .pagination-btn:hover:not(:disabled) {
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .pagination-btn i {
            font-size: 12px;
        }

        .page-size-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-size-selector label {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }

        .page-size-selector select {
            padding: 6px 30px 6px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            background: #fff;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
        }

        .pagination-ellipsis {
            padding: 8px 12px;
            color: #9ca3af;
        }

        @media (max-width: 768px) {
            .pagination-wrapper {
                flex-direction: column;
                gap: 15px;
            }

            .pagination-controls {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

@include('layouts.navbar')
@include('layouts.sidebar')
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="main-container">
<div class="main-content">

<h2>Data Pengguna</h2>

<!-- ================= SEARCH & ACTION ================= -->
<div class="buttons">
    <form action="{{ route('users.index') }}" method="GET" class="search-form" id="searchForm">
        <div class="search-input-wrapper">
            <input type="text"
                   class="form-control"
                   placeholder="Cari pengguna..."
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

        <button type="button" class="btn-add" data-toggle="modal" data-target="#addUserModal">
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
    <th>ID Pengguna</th>
    <th>Nama</th>
    <th>Username</th>
    <th>Role</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody id="userTableBody">

@forelse($users as $user)
<tr>
    <td data-label="ID Pengguna">
        <span class="id-badge">{{ $user->id_user }}</span>
    </td>
    <td data-label="Nama">
        <span class="product-name">{{ $user->nama_user }}</span>
    </td>
    <td data-label="Username">{{ $user->username }}</td>
    <td data-label="Role">
        <span class="badge-role {{ $user->role == 'admin' ? 'badge-admin' : 'badge-kasir' }}">
            {{ ucfirst($user->role) }}
        </span>
    </td>
    <td data-label="Aksi">
        <div class="action-buttons">
            <button class="btn-edit"
                    data-toggle="modal"
                    data-target="#editUserModal{{ $user->id_user }}"
                    title="Edit">
                <i class="fas fa-edit"></i>
            </button>

            <button class="btn-delete"
                    onclick="confirmDelete('{{ $user->id_user }}')"
                    title="Hapus">
                <i class="fas fa-trash-alt"></i>
            </button>

            <form id="delete-form-{{ $user->id_user }}"
                  action="{{ route('users.destroy', $user->id_user) }}"
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
    <td colspan="5" class="text-center">
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Tidak ada data pengguna</p>
        </div>
    </td>
</tr>
@endforelse

</tbody>
</table>
</div>

<!-- ================= PAGINATION SECTION ================= -->
<div class="pagination-wrapper">
    <div class="page-size-selector">
        <label for="pageSize">Tampilkan:</label>
        <select id="pageSize">
            <option value="10">10 baris</option>
            <option value="20" selected>20 baris</option>
            <option value="50">50 baris</option>
            <option value="100">100 baris</option>
        </select>
    </div>

    <div class="pagination-info">
        Menampilkan <strong id="showingStart">1</strong> - <strong id="showingEnd">20</strong> dari <strong id="totalItems">0</strong> data
    </div>

    <div class="pagination-controls" id="paginationControls">
        <button class="pagination-btn" id="firstPage" title="Halaman Pertama">
            <i class="fas fa-angle-double-left"></i>
        </button>
        <button class="pagination-btn" id="prevPage" title="Sebelumnya">
            <i class="fas fa-angle-left"></i>
        </button>
        <div id="pageNumbers"></div>
        <button class="pagination-btn" id="nextPage" title="Selanjutnya">
            <i class="fas fa-angle-right"></i>
        </button>
        <button class="pagination-btn" id="lastPage" title="Halaman Terakhir">
            <i class="fas fa-angle-double-right"></i>
        </button>
    </div>
</div>

</div>
</div>

<!-- ================= MODAL TAMBAH ================= -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
<div class="modal-dialog" role="document">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title" id="addUserModalLabel">
        <i class="bi bi-plus-circle"></i>
        Tambah Pengguna
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body">
<form action="{{ route('users.store') }}" method="POST" id="addUserForm">
@csrf

<div class="form-group">
    <label for="nama_user">Nama <span class="text-danger">*</span></label>
    <input type="text"
           class="form-control @error('nama_user') is-invalid @enderror"
           id="nama_user"
           name="nama_user"
           placeholder="Masukkan nama"
           value="{{ old('nama_user') }}"
           required>
    @error('nama_user')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="username">Username <span class="text-danger">*</span></label>
    <input type="text"
           class="form-control @error('username') is-invalid @enderror"
           id="username"
           name="username"
           placeholder="Masukkan username"
           value="{{ old('username') }}"
           required>
    @error('username')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="password">Password <span class="text-danger">*</span></label>
    <input type="password"
           class="form-control @error('password') is-invalid @enderror"
           id="password"
           name="password"
           placeholder="Masukkan password (min. 8 karakter)"
           required>
    @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="role">Role <span class="text-danger">*</span></label>
    <select class="form-control @error('role') is-invalid @enderror"
            id="role"
            name="role"
            required>
        <option value="">Pilih Role</option>
        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
        <option value="kasir" {{ old('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
    </select>
    @error('role')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
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
@foreach($users as $user)
<div class="modal fade" id="editUserModal{{ $user->id_user }}" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel{{ $user->id_user }}" aria-hidden="true">
<div class="modal-dialog" role="document">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title" id="editUserModalLabel{{ $user->id_user }}">
        <i class="bi bi-pencil-square"></i>
        Edit Pengguna
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body">
<form action="{{ route('users.update', $user->id_user) }}" method="POST">
@csrf
@method('PUT')

<div class="form-group">
    <label for="nama_user{{ $user->id_user }}">Nama <span class="text-danger">*</span></label>
    <input type="text"
           class="form-control"
           id="nama_user{{ $user->id_user }}"
           name="nama_user"
           value="{{ $user->nama_user }}"
           required>
</div>

<div class="form-group">
    <label for="username{{ $user->id_user }}">Username <span class="text-danger">*</span></label>
    <input type="text"
           class="form-control"
           id="username{{ $user->id_user }}"
           name="username"
           value="{{ $user->username }}"
           required>
</div>

<div class="form-group">
    <label for="password{{ $user->id_user }}">Password</label>
    <input type="password"
           class="form-control"
           id="password{{ $user->id_user }}"
           name="password"
           placeholder="Kosongkan jika tidak ingin mengubah password">
    <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah password</small>
</div>

<div class="form-group">
    <label for="role{{ $user->id_user }}">Role <span class="text-danger">*</span></label>
    <select class="form-control"
            id="role{{ $user->id_user }}"
            name="role"
            required>
        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
        <option value="kasir" {{ $user->role == 'kasir' ? 'selected' : '' }}>Kasir</option>
    </select>
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

@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: '{{ session('error') }}',
    timer: 3000,
    showConfirmButton: true
});
</script>
@endif

@if($errors->any())
<script>
Swal.fire({
    icon: 'error',
    title: 'Validasi Gagal',
    html: '@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach',
    showConfirmButton: true
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
        text: 'Data pengguna ini akan dihapus permanen!',
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

$(document).ready(function() {

    // ====================================
    // LIVE SEARCH FUNCTIONALITY
    // ====================================
    let searchTimeout;
    const searchInput = $('#searchInput');
    const searchLoading = $('#searchLoading');
    const searchIcon = $('#searchIcon');
    const clearButton = $('#clearSearch');
    const searchInfo = $('#searchInfo');
    const searchTerm = $('#searchTerm');
    const tableBody = $('#userTableBody');

    searchInput.on('input', function() {
        const query = $(this).val().trim();
        clearTimeout(searchTimeout);

        if (query.length > 0) {
            clearButton.show();
        } else {
            clearButton.hide();
            searchInfo.hide();
        }

        searchTimeout = setTimeout(function() {
            performSearch(query);
        }, 300);
    });

    clearButton.on('click', function() {
        searchInput.val('');
        clearButton.hide();
        searchInfo.hide();
        performSearch('');
    });

    function performSearch(query) {
        searchIcon.hide();
        searchLoading.show();

        $.ajax({
            url: '{{ route("users.index") }}',
            type: 'GET',
            data: { q: query },
            success: function(response) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(response, 'text/html');
                const newTableBody = doc.querySelector('#userTableBody');

                if (newTableBody) {
                    tableBody.html(newTableBody.innerHTML);
                    // Reinitialize pagination after search
                    initPagination();
                }

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

    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        return false;
    });

    // Debug: Log form submission
    $('#addUserForm').on('submit', function(e) {
        console.log('Form submitted');
        console.log('Form data:', $(this).serialize());
    });

    // ====================================
    // PAGINATION FUNCTIONALITY
    // ====================================
    let currentPage = 1;
    let itemsPerPage = 20;
    let allRows = [];

    function initPagination() {
        // Get all table rows
        allRows = Array.from(document.querySelectorAll('#userTableBody tr'));

        // Update total items
        document.getElementById('totalItems').textContent = allRows.length;

        // Reset to first page
        currentPage = 1;

        // Render pagination
        renderPagination();
    }

    function renderPagination() {
        const totalPages = Math.ceil(allRows.length / itemsPerPage);
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;

        // Hide all rows
        allRows.forEach(row => row.style.display = 'none');

        // Show only current page rows
        const visibleRows = allRows.slice(start, end);
        visibleRows.forEach(row => row.style.display = '');

        // Update showing info
        const actualStart = allRows.length > 0 ? start + 1 : 0;
        const actualEnd = Math.min(end, allRows.length);
        document.getElementById('showingStart').textContent = actualStart;
        document.getElementById('showingEnd').textContent = actualEnd;

        // Update buttons state
        document.getElementById('firstPage').disabled = currentPage === 1;
        document.getElementById('prevPage').disabled = currentPage === 1;
        document.getElementById('nextPage').disabled = currentPage === totalPages || totalPages === 0;
        document.getElementById('lastPage').disabled = currentPage === totalPages || totalPages === 0;

        // Render page numbers
        renderPageNumbers(totalPages);
    }

    function renderPageNumbers(totalPages) {
        const pageNumbersContainer = document.getElementById('pageNumbers');
        pageNumbersContainer.innerHTML = '';

        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) {
                pageNumbersContainer.appendChild(createPageButton(i));
            }
        } else {
            pageNumbersContainer.appendChild(createPageButton(1));

            if (currentPage > 3) {
                pageNumbersContainer.appendChild(createEllipsis());
            }

            const startPage = Math.max(2, currentPage - 1);
            const endPage = Math.min(totalPages - 1, currentPage + 1);

            for (let i = startPage; i <= endPage; i++) {
                pageNumbersContainer.appendChild(createPageButton(i));
            }

            if (currentPage < totalPages - 2) {
                pageNumbersContainer.appendChild(createEllipsis());
            }

            if (totalPages > 1) {
                pageNumbersContainer.appendChild(createPageButton(totalPages));
            }
        }
    }

    function createPageButton(pageNum) {
        const button = document.createElement('button');
        button.className = 'pagination-btn' + (pageNum === currentPage ? ' active' : '');
        button.textContent = pageNum;
        button.addEventListener('click', () => {
            currentPage = pageNum;
            renderPagination();
        });
        return button;
    }

    function createEllipsis() {
        const span = document.createElement('span');
        span.className = 'pagination-ellipsis';
        span.textContent = '...';
        return span;
    }

    // Navigation button listeners
    document.getElementById('firstPage').addEventListener('click', () => {
        currentPage = 1;
        renderPagination();
    });

    document.getElementById('prevPage').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderPagination();
        }
    });

    document.getElementById('nextPage').addEventListener('click', () => {
        const totalPages = Math.ceil(allRows.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderPagination();
        }
    });

    document.getElementById('lastPage').addEventListener('click', () => {
        const totalPages = Math.ceil(allRows.length / itemsPerPage);
        currentPage = totalPages;
        renderPagination();
    });

    // Page size change
    document.getElementById('pageSize').addEventListener('change', function() {
        itemsPerPage = parseInt(this.value);
        currentPage = 1;
        renderPagination();
    });

    // Initialize pagination on page load
    initPagination();

});
</script>
</body>
</html>