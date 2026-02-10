<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang | Sistem Inventory dan Kasir</title>
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
    <!-- Navigation & Sidebar -->
    @include('layouts.navbar')
    @include('layouts.sidebar')
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="main-container">
        <div class="main-content">
            <h2>Data Barang</h2>
            <div class="buttons">
                <form action="{{ route('items.index') }}" method="GET" class="search-form" id="searchForm">
                    <!-- Search Input -->
                    <div class="search-input-wrapper">
                        <input 
                            type="text" 
                            class="form-control" 
                            placeholder="Cari barang..." 
                            name="q" 
                            value="{{ request('q') }}" 
                            id="searchInput" 
                            autocomplete="off">
                        <span class="search-icon">
                            <i class="bi bi-arrow-clockwise spin" id="searchLoading" style="display: none;"></i>
                        </span>
                    </div>

                    <!-- Clear Search Button -->
                    <button 
                        type="button" 
                        class="btn-clear" 
                        id="clearSearch" 
                        style="{{ request('q') ? '' : 'display: none;' }}">
                        <i class="bi bi-x-circle"></i>
                        <span>Clear</span>
                    </button>

                    <!-- Add Item Button -->
                    <button 
                        type="button" 
                        class="btn-add" 
                        data-toggle="modal" 
                        data-target="#addItemModal">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah Data</span>
                    </button>
                </form>

                <!-- Search Info -->
                <div class="search-info" id="searchInfo" style="display: none;">
                    Menampilkan hasil pencarian untuk: <strong id="searchTerm"></strong>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive">
                <table class="table-main">
                    <thead>
                        <tr>
                            <th>ID Produk</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Brand</th>
                            <th>Supplier</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th>FSN</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Diskon</th>
                            <th>Harga Diskon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemTableBody">
                        @forelse($items as $item)
                        <tr>
                            <td data-label="ID Produk">
                                <span class="id-badge">{{ $item->id_produk }}</span>
                            </td>
                            <td data-label="Nama Produk">
                                <span class="product-name">{{ $item->nama_produk }}</span>
                            </td>
                            <td data-label="Kategori">{{ $item->kategori->kategori }}</td>
                            <td data-label="Brand">{{ $item->brand->brand }}</td>
                            <td data-label="Supplier">{{ $item->supplier->nama_supplier ?? '-' }}</td>
                            <td data-label="Stok">
                                <span class="stock-number">{{ $item->stok }}</span>
                            </td>
                            <td data-label="Satuan">{{ $item->satuan }}</td>
                            <td data-label="FSN">
                                <div class="fsn-container">
                                    @if($item->FSN == 'NA')
                                        <span class="status-badge status-normal fsn-badge">NA</span>
                                        <span class="fsn-tor" style="font-size: 9px; color: #999;">
                                            Umur: {{ $item->umur_hari }} hari
                                        </span>
                                    @else
                                        <span class="status-badge fsn-badge
                                            @if($item->FSN == 'F') status-normal 
                                            @elseif($item->FSN == 'S') status-medium 
                                            @elseif($item->FSN == 'N') status-high 
                                            @endif">
                                            {{ $item->FSN }}
                                        </span>
                                        @if($item->tor_value !== null && $item->tor_value > 0)
                                            <span class="fsn-tor">
                                                TOR: {{ number_format($item->tor_value, 2) }}
                                            </span>
                                        @endif
                                        @if($item->consecutive_n_months > 0)
                                            <span class="fsn-tor" style="color: #ef4444;">
                                                {{ $item->consecutive_n_months }}x N
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td data-label="Harga Beli">Rp {{ number_format($item->modal, 0, ',', '.') }}</td>
                            <td data-label="Harga Jual">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                            <td data-label="Diskon">{{ $item->diskon }}%</td>
                            <td data-label="Harga Diskon">Rp {{ number_format($item->harga_setelah_diskon, 0, ',', '.') }}</td>
                            <td data-label="Aksi">
                                <div class="action-buttons">
                                    <button 
                                        type="button" 
                                        class="btn-edit" 
                                        data-toggle="modal" 
                                        data-target="#editItemModal{{ $item->id_produk }}" 
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn-delete" 
                                        onclick="confirmDelete('{{ $item->id_produk }}')" 
                                        title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <form 
                                        id="delete-form-{{ $item->id_produk }}" 
                                        action="{{ route('items.destroy', $item->id_produk) }}" 
                                        method="POST" 
                                        style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Tidak ada data barang</p>
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

    <!-- ========================================
         MODAL: ADD ITEM
    ========================================= -->
    <div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">
                        <i class="bi bi-plus-circle"></i> Tambah Barang
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('items.store') }}" method="POST" id="addItemForm">
                        @csrf

                        <!-- Nama Produk -->
                        <div class="form-group">
                            <label for="nama_produk">
                                Nama Produk <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="nama_produk" 
                                name="nama_produk" 
                                placeholder="Masukkan nama produk" 
                                required>
                        </div>

                        <!-- Tanggal Masuk -->
                        <div class="form-group">
                            <label for="tanggal_masuk">
                                Tanggal Masuk <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="date" 
                                class="form-control date-input" 
                                id="tanggal_masuk" 
                                name="tanggal_masuk" 
                                value="{{ now()->format('Y-m-d') }}" 
                                required>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Tanggal barang pertama kali masuk ke inventori (default: hari ini)
                            </small>
                        </div>

                        <!-- Kategori -->
                        <div class="form-group">
                            <label for="id_kategori">
                                Kategori <span style="color: red;">*</span>
                            </label>
                            <select name="id_kategori" id="id_kategori" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id_kategori }}">{{ $category->kategori }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Brand -->
                        <div class="form-group">
                            <label for="id_brand">
                                Brand <span style="color: red;">*</span>
                            </label>
                            <select name="id_brand" id="id_brand" class="form-control" required>
                                <option value="">-- Pilih Brand --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id_brand }}">{{ $brand->brand }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Supplier -->
                        <div class="form-group">
                            <label for="id_supplier">
                                Supplier <span style="color: red;">*</span>
                            </label>
                            <select name="id_supplier" id="id_supplier" class="form-control" required>
                                <option value="">-- Pilih Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id_supplier }}">{{ $supplier->nama_supplier }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Harga Jual -->
                        <div class="form-group">
                            <label for="harga_jual">
                                Harga Jual <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control rupiah" 
                                id="harga_jual" 
                                name="harga_jual" 
                                placeholder="0" 
                                autocomplete="off" 
                                required>
                        </div>

                        <!-- Stok -->
                        <div class="form-group">
                            <label for="stok">
                                Stok <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="number" 
                                class="form-control" 
                                id="stok" 
                                name="stok" 
                                placeholder="0" 
                                min="0" 
                                required>
                        </div>

                        <!-- Satuan -->
                        <div class="form-group">
                            <label for="satuan">
                                Satuan <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="satuan" 
                                name="satuan" 
                                placeholder="Pcs, Kg, Box, dll" 
                                required>
                        </div>

                        <!-- Modal -->
                        <div class="form-group">
                            <label for="modal">
                                Modal (Harga Beli) <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control rupiah" 
                                id="modal" 
                                name="modal" 
                                placeholder="0" 
                                autocomplete="off" 
                                required>
                        </div>

                        <!-- Diskon -->
                        <div class="form-group">
                            <label for="diskon">
                                Diskon (%) <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="number" 
                                class="form-control diskon" 
                                id="diskon" 
                                name="diskon" 
                                value="0" 
                                min="0" 
                                max="100" 
                                step="1" 
                                required>
                        </div>

                        <!-- Footer Buttons -->
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

    <!-- ========================================
         MODAL: EDIT ITEM
    ========================================= -->
    @foreach($items as $item)
    <div class="modal fade" id="editItemModal{{ $item->id_produk }}" tabindex="-1" role="dialog" aria-labelledby="editItemModalLabel{{ $item->id_produk }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editItemModalLabel{{ $item->id_produk }}">
                        <i class="bi bi-pencil-square"></i> Edit Barang
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('items.update', $item->id_produk) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Nama Produk -->
                        <div class="form-group">
                            <label for="nama_produk{{ $item->id_produk }}">
                                Nama Produk <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="nama_produk{{ $item->id_produk }}" 
                                name="nama_produk" 
                                value="{{ $item->nama_produk }}" 
                                required>
                        </div>

                        <!-- Tanggal Masuk -->
                        <div class="form-group">
                            <label for="tanggal_masuk{{ $item->id_produk }}">
                                Tanggal Masuk <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="date" 
                                class="form-control date-input" 
                                id="tanggal_masuk{{ $item->id_produk }}" 
                                name="tanggal_masuk" 
                                value="{{ $item->tanggal_masuk ? $item->tanggal_masuk->format('Y-m-d') : now()->format('Y-m-d') }}" 
                                required>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Ubah jika tanggal masuk tidak sesuai
                            </small>
                        </div>

                        <!-- Kategori -->
                        <div class="form-group">
                            <label for="id_kategori{{ $item->id_produk }}">
                                Kategori <span style="color: red;">*</span>
                            </label>
                            <select name="id_kategori" id="id_kategori{{ $item->id_produk }}" class="form-control" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id_kategori }}" {{ $category->id_kategori == $item->id_kategori ? 'selected' : '' }}>
                                        {{ $category->kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Brand -->
                        <div class="form-group">
                            <label for="id_brand{{ $item->id_produk }}">
                                Brand <span style="color: red;">*</span>
                            </label>
                            <select name="id_brand" id="id_brand{{ $item->id_produk }}" class="form-control" required>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id_brand }}" {{ $brand->id_brand == $item->id_brand ? 'selected' : '' }}>
                                        {{ $brand->brand }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Supplier -->
                        <div class="form-group">
                            <label for="id_supplier{{ $item->id_produk }}">
                                Supplier <span style="color: red;">*</span>
                            </label>
                            <select name="id_supplier" id="id_supplier{{ $item->id_produk }}" class="form-control" required>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id_supplier }}" {{ $supplier->id_supplier == $item->id_supplier ? 'selected' : '' }}>
                                        {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Harga Jual -->
                        <div class="form-group">
                            <label for="harga_jual{{ $item->id_produk }}">
                                Harga Jual <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control rupiah" 
                                id="harga_jual{{ $item->id_produk }}" 
                                name="harga_jual" 
                                value="{{ number_format($item->harga_jual, 0, ',', '.') }}" 
                                autocomplete="off" 
                                required>
                        </div>

                        <!-- Stok -->
                        <div class="form-group">
                            <label for="stok{{ $item->id_produk }}">
                                Stok <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="number" 
                                class="form-control" 
                                id="stok{{ $item->id_produk }}" 
                                name="stok" 
                                value="{{ $item->stok }}" 
                                min="0" 
                                required>
                        </div>

                        <!-- Satuan -->
                        <div class="form-group">
                            <label for="satuan{{ $item->id_produk }}">
                                Satuan <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="satuan{{ $item->id_produk }}" 
                                name="satuan" 
                                value="{{ $item->satuan }}" 
                                required>
                        </div>

                        <!-- Modal -->
                        <div class="form-group">
                            <label for="modal{{ $item->id_produk }}">
                                Modal (Harga Beli) <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control rupiah" 
                                id="modal{{ $item->id_produk }}" 
                                name="modal" 
                                value="{{ number_format($item->modal, 0, ',', '.') }}" 
                                autocomplete="off" 
                                required>
                        </div>

                        <!-- Diskon (READ ONLY - Otomatis dari FSN) -->
                        <div class="form-group">
                            <label for="diskon{{ $item->id_produk }}">
                                Diskon (%) <span style="color: #666; font-weight: normal;">(Otomatis dari FSN)</span>
                            </label>
                            <input
                                type="number"
                                class="form-control"
                                id="diskon{{ $item->id_produk }}"
                                value="{{ $item->diskon }}"
                                min="0"
                                max="100"
                                step="1"
                                readonly
                                style="background-color: #f8f9fa; cursor: not-allowed;">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i>
                                Diskon diatur otomatis berdasarkan analisis FSN. Tidak dapat diubah manual.
                            </small>
                        </div>

                        <!-- Footer Buttons -->
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

    

    <!-- ========================================
         SESSION MESSAGES (SweetAlert)
    ========================================= -->
    @if (session('success'))
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

    @if (session('error'))
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

    <!-- ========================================
         JAVASCRIPT LIBRARIES
    ========================================= -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ========================================
         CUSTOM JAVASCRIPT
    ========================================= -->
    <script>
        // =====================================
        // SIDEBAR TOGGLE
        // =====================================
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }

        // =====================================
        // CONFIRM DELETE
        // =====================================
        function confirmDelete(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data barang ini akan dihapus permanen!",
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

        // =====================================
        // DOCUMENT READY
        // =====================================
        $(document).ready(function() {
            
            // ====================================
            // DATE VALIDATION FUNCTIONS
            // ====================================
            function getTodayDate() {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            // Auto-set today's date when add modal opens
            $('#addItemModal').on('shown.bs.modal', function () {
                $('#tanggal_masuk').val(getTodayDate());
            });

            // Validate date input
            $(document).on('change', '.date-input', function() {
                const selectedDate = new Date($(this).val());
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                selectedDate.setHours(0, 0, 0, 0);

                if (selectedDate > today) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal Tidak Valid',
                        text: 'Tanggal masuk tidak boleh melebihi hari ini!',
                        confirmButtonText: 'OK'
                    });
                    $(this).val(getTodayDate());
                }
            });

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
            const tableBody = $('#itemTableBody');

            // Search input handler
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

            // Clear search button
            clearButton.on('click', function() {
                searchInput.val('');
                clearButton.hide();
                searchInfo.hide();
                performSearch('');
            });

            // Perform search function
            function performSearch(query) {
                searchIcon.hide();
                searchLoading.show();

                $.ajax({
                    url: '{{ route("items.index") }}',
                    type: 'GET',
                    data: { q: query },
                    success: function(response) {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(response, 'text/html');
                        const newTableBody = doc.querySelector('#itemTableBody');

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

            // Prevent form submission
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
                // Get all table rows
                allRows = Array.from(document.querySelectorAll('#itemTableBody tr'));
                
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

    <script>
        // =====================================
        // RUPIAH FORMAT (AUTO DOT SEPARATOR)
        // =====================================
        document.querySelectorAll('.rupiah').forEach(function(input) {
            // Format on input
            input.addEventListener('input', function () {
                let value = this.value.replace(/[^0-9]/g, '');
                this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            });

            // Remove dots before form submit
            input.closest('form').addEventListener('submit', function () {
                input.value = input.value.replace(/\./g, '');
            });
        });

        // =====================================
        // DISCOUNT INPUT (NUMBERS ONLY, MAX 100)
        // =====================================
        document.querySelectorAll('.diskon').forEach(function(input) {
            input.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value > 100) {
                    this.value = 100;
                }
            });
        });
    </script>
</body>
</html>