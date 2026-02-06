<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengguna | Sistem Inventory dan Kasir</title>

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

// ================= LIVE SEARCH FUNCTIONALITY ================= 
$(document).ready(function() {
    let searchTimeout;
    const searchInput = $('#searchInput');
    const searchLoading = $('#searchLoading');
    const searchIcon = $('#searchIcon');
    const clearButton = $('#clearSearch');
    const searchInfo = $('#searchInfo');
    const searchTerm = $('#searchTerm');
    const tableBody = $('#userTableBody');

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
            url: '{{ route("users.index") }}',
            type: 'GET',
            data: { q: query },
            success: function(response) {
                // Parse HTML response
                const parser = new DOMParser();
                const doc = parser.parseFromString(response, 'text/html');
                const newTableBody = doc.querySelector('#userTableBody');
                
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

    // Debug: Log form submission
    $('#addUserForm').on('submit', function(e) {
        console.log('Form submitted');
        console.log('Form data:', $(this).serialize());
    });
});
</script>
</body>
</html>