<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori | Sistem Inventory dan Kasir</title>
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
            <h2>Data Kategori</h2>
            
            <div class="buttons">
                <form action="{{ route('categories.index') }}" method="GET" class="search-form" id="searchForm">
                    <div class="search-input-wrapper">
                        <input type="text" class="form-control" placeholder="Cari kategori..." name="q" value="{{ request('q') }}" id="searchInput" autocomplete="off">
                        <span class="search-icon">
                            <i class="bi bi-arrow-clockwise spin" id="searchLoading" style="display: none;"></i>
                        </span>
                    </div>
                    <button type="button" class="btn-clear" id="clearSearch" style="{{ request('q') ? '' : 'display: none;' }}">
                        <i class="bi bi-x-circle"></i>
                        <span>Clear</span>
                    </button>
                    <button type="button" class="btn-add" data-toggle="modal" data-target="#addCategoryModal">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah Data</span>
                    </button> 
                </form>
                <div class="search-info" id="searchInfo" style="display: none;">
                    Menampilkan hasil pencarian untuk: <strong id="searchTerm"></strong>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-main">
                    <thead>
                        <tr>
                            <th>ID Kategori</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="categoryTableBody">
                        @forelse($categories as $category)
                        <tr>
                            <td data-label="ID Kategori">
                                <span class="id-badge">{{ $category->id_kategori }}</span>
                            </td>
                            <td data-label="Kategori">
                                <span class="product-name">{{ $category->kategori }}</span>
                            </td>
                            <td data-label="Aksi">
                                <div class="action-buttons">
                                    <button type="button" class="btn-edit" data-toggle="modal" data-target="#editCategoryModal{{ $category->id_kategori }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn-delete" onclick="confirmDelete('{{ $category->id_kategori }}')" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <form id="delete-form-{{ $category->id_kategori }}" action="{{ route('categories.destroy', $category->id_kategori) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Tidak ada data kategori</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">
                        <i class="bi bi-plus-circle"></i>
                        Tambah Kategori
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="kategori">Nama Kategori</label>
                            <input type="text" class="form-control" id="kategori" name="kategori" placeholder="Masukkan nama kategori" required>
                        </div>
                        <div class="modal-footer-custom">
                            <button type="button" class="btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn-success">
                                <i class="bi bi-check-circle"></i>
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @foreach($categories as $category)
    <div class="modal fade" id="editCategoryModal{{ $category->id_kategori }}" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel{{ $category->id_kategori }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel{{ $category->id_kategori }}">
                        <i class="bi bi-pencil-square"></i>
                        Edit Kategori
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('categories.update', $category->id_kategori) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="kategori{{ $category->id_kategori }}">Nama Kategori</label>
                            <input type="text" class="form-control" id="kategori{{ $category->id_kategori }}" name="kategori" value="{{ $category->kategori }}" required>
                        </div>
                        <div class="modal-footer-custom">
                            <button type="button" class="btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn-success">
                                <i class="bi bi-check-circle"></i>
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    @if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ session('success') }}',
            timer: 2000,
            showConfirmButton: false
        })
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
                text: "Data kategori ini akan dihapus permanen!",
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
            let searchTimeout;
            const searchInput = $('#searchInput');
            const searchLoading = $('#searchLoading');
            const searchIcon = $('#searchIcon');
            const clearButton = $('#clearSearch');
            const searchInfo = $('#searchInfo');
            const searchTerm = $('#searchTerm');
            const tableBody = $('#categoryTableBody');

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
                    url: '{{ route("categories.index") }}',
                    type: 'GET',
                    data: { q: query },
                    success: function(response) {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(response, 'text/html');
                        const newTableBody = doc.querySelector('#categoryTableBody');
                        
                        if (newTableBody) {
                            tableBody.html(newTableBody.innerHTML);
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
        });
    </script>
</body>
</html>


