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

            <!-- PAGINATION SECTION -->
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

    <!-- MODAL: ADD CATEGORY -->
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

    <!-- MODAL: EDIT CATEGORY -->
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
            // ====================================
            // AJAX SEARCH FUNCTIONALITY
            // ====================================
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

            // ====================================
            // PAGINATION FUNCTIONALITY
            // ====================================
            let currentPage = 1;
            let itemsPerPage = 20;
            let allRows = [];

            function initPagination() {
                // Get all table rows (exclude empty state row)
                allRows = Array.from(document.querySelectorAll('#categoryTableBody tr')).filter(row => {
                    return !row.querySelector('.empty-state');
                });
                
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
                    // Show all pages
                    for (let i = 1; i <= totalPages; i++) {
                        pageNumbersContainer.appendChild(createPageButton(i));
                    }
                } else {
                    // Show first page
                    pageNumbersContainer.appendChild(createPageButton(1));

                    if (currentPage > 3) {
                        pageNumbersContainer.appendChild(createEllipsis());
                    }

                    // Show pages around current page
                    const startPage = Math.max(2, currentPage - 1);
                    const endPage = Math.min(totalPages - 1, currentPage + 1);

                    for (let i = startPage; i <= endPage; i++) {
                        pageNumbersContainer.appendChild(createPageButton(i));
                    }

                    if (currentPage < totalPages - 2) {
                        pageNumbersContainer.appendChild(createEllipsis());
                    }

                    // Show last page
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

            // Event listeners for pagination controls
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