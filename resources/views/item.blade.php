<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang | Sistem Inventory dan Kasir</title>
    
    <!-- CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            <!-- Action Buttons & Search -->
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

                    <!-- Calculate FSN Button -->
                    <button 
                        type="button" 
                        class="btn-info" 
                        data-toggle="modal" 
                        data-target="#calculateFSNModal">
                        <i class="fas fa-calculator"></i>
                        <span>Hitung FSN</span>
                    </button>

                    <!-- FSN Report Link -->
                    <a href="{{ route('fsn.report') }}" class="btn-info">
                        <i class="fas fa-chart-bar"></i>
                        <span>Laporan FSN</span>
                    </a>
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
                                @if($item->FSN == 'NA')
                                    <span class="status-badge status-normal">NA</span>
                                @else
                                    <span class="status-badge 
                                        @if($item->FSN == 'F') status-normal 
                                        @elseif($item->FSN == 'S') status-medium 
                                        @elseif($item->FSN == 'N') status-high 
                                        @endif">
                                        {{ $item->FSN }}
                                    </span>
                                    @if($item->tor_value !== null)
                                        <br><small style="color: #666; font-size: 10px;">
                                            TOR: {{ number_format($item->tor_value, 2) }}
                                        </small>
                                    @endif
                                @endif
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

                        <!-- Diskon -->
                        <div class="form-group">
                            <label for="diskon{{ $item->id_produk }}">
                                Diskon (%) <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="number" 
                                class="form-control diskon" 
                                id="diskon{{ $item->id_produk }}" 
                                name="diskon" 
                                value="{{ $item->diskon }}" 
                                min="0" 
                                max="100" 
                                step="1" 
                                required>
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
         MODAL: CALCULATE FSN
    ========================================= -->
    <div class="modal fade" id="calculateFSNModal" tabindex="-1" role="dialog" aria-labelledby="calculateFSNModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="calculateFSNModalLabel">
                        <i class="fas fa-calculator"></i> Hitung FSN Analysis
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('items.calculate.fsn') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <!-- Periode Selection -->
                        <div class="form-group">
                            <label for="periode"><strong>Periode Pengamatan (Hari)</strong></label>
                            <select name="periode" id="periode" class="form-control">
                                <option value="30">30 Hari (1 Bulan)</option>
                                <option value="60">60 Hari (2 Bulan)</option>
                                <option value="90" selected>90 Hari (3 Bulan)</option>
                                <option value="180">180 Hari (6 Bulan)</option>
                                <option value="365">365 Hari (1 Tahun)</option>
                            </select>
                        </div>

                        <!-- Info Alert -->
                        <div class="alert alert-info" style="padding: 12px; background: rgba(14, 165, 233, 0.1); border-left: 3px solid var(--info-color); border-radius: 6px; margin-bottom: 12px;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong>
                            <ul style="margin: 10px 0 0 0; padding-left: 20px; font-size: 13px;">
                                <li>Sistem akan menghitung FSN untuk produk yang sudah <strong>berumur minimal 30 hari</strong></li>
                                <li>Produk baru (<30 hari) akan tetap berstatus <span class="status-badge status-normal">NA</span></li>
                                <li>Perhitungan berdasarkan data penjualan dalam periode yang dipilih</li>
                                <li>Kategori: <span class="status-badge status-normal">F</span> = TOR > 3, <span class="status-badge status-medium">S</span> = 1 ≤ TOR ≤ 3, <span class="status-badge status-high">N</span> = TOR < 1</li>
                            </ul>
                        </div>

                        <!-- Warning Alert -->
                        <div class="alert alert-warning" style="padding: 12px; background: rgba(245, 158, 11, 0.1); border-left: 3px solid var(--warning-color); border-radius: 6px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Catatan:</strong>
                            <ul style="margin: 5px 0 0 0; padding-left: 20px; font-size: 13px;">
                                <li>Pastikan produk sudah melewati periode observasi minimal (30 hari)</li>
                                <li>Data penjualan harus lengkap untuk hasil yang akurat</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="modal-footer-custom">
                        <button type="button" class="btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn-success">
                            <i class="fas fa-calculator"></i> Hitung Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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