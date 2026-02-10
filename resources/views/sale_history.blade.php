<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Penjualan | Sistem Inventory dan Kasir</title>
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
            <h2>History Penjualan</h2>
            
            <div class="buttons">
                <form action="{{ route('sale.history') }}" method="GET" class="search-form" id="searchForm">
                    <div class="search-input-wrapper">
                        <input 
                            type="text" 
                            class="form-control" 
                            placeholder="Cari ID transaksi, kasir, atau customer..." 
                            name="q" 
                            value="{{ request('q') }}" 
                            id="searchInput" 
                            autocomplete="off"
                        >
                        <span class="search-icon">
                            <i class="bi bi-search" id="searchIcon"></i>
                            <i class="bi bi-arrow-clockwise spin" id="searchLoading" style="display: none;"></i>
                        </span>
                    </div>
                    <input 
                        type="date" 
                        name="tanggal" 
                        class="form-control" 
                        value="{{ request('tanggal') }}" 
                        id="dateFilter"
                        style="max-width: 180px;"
                    >
                    <button type="button" class="btn-clear" id="clearSearch" style="{{ request('q') || request('tanggal') ? '' : 'display: none;' }}">
                        <i class="bi bi-x-circle"></i>
                        <span>Clear</span>
                    </button>
                    <button type="button" class="btn-primary" id="filterButton">
                        <i class="bi bi-funnel"></i>
                        <span>Filter</span>
                    </button>
                </form>
                <div class="search-info" id="searchInfo" style="display: {{ request('q') || request('tanggal') ? 'block' : 'none' }};">
                    @if(request('q'))
                        Hasil pencarian: <strong>{{ request('q') }}</strong>
                    @endif
                    @if(request('tanggal'))
                        {{ request('q') ? ' | ' : '' }}Tanggal: <strong>{{ \Carbon\Carbon::parse(request('tanggal'))->format('d/m/Y') }}</strong>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-main">
                    <thead>
                        <tr>
                            <th>ID Penjualan</th>
                            <th>Tanggal</th>
                            <th>Kasir</th>
                            <th>Customer</th>
                            <th>Total Belanja</th>
                            <th>Jumlah Bayar</th>
                            <th>Kembalian</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="saleTableBody">
                        @forelse($sales as $sale)
                        <tr>
                            <td data-label="ID Penjualan">
                                <span class="id-badge">{{ $sale->id_penjualan }}</span>
                            </td>
                            <td data-label="Tanggal">{{ \Carbon\Carbon::parse($sale->tanggal_transaksi)->format('d/m/Y H:i') }}</td>
                            <td data-label="Kasir">
                                <span class="product-name">{{ $sale->user->name ?? $sale->id_user }}</span>
                            </td>
                            <td data-label="Customer">
                                <span class="product-name">{{ $sale->customer->nama_pelanggan ?? '-' }}</span>
                            </td>
                            <td data-label="Total Belanja">
                                <span class="stock-number">Rp {{ number_format($sale->total_belanja, 0, ',', '.') }}</span>
                            </td>
                            <td data-label="Jumlah Bayar">Rp {{ number_format($sale->jumlah_bayar, 0, ',', '.') }}</td>
                            <td data-label="Kembalian">
                                <span class="text-success" style="font-weight: 600;">Rp {{ number_format($sale->kembalian, 0, ',', '.') }}</span>
                            </td>
                            <td data-label="Aksi">
                                <div class="action-buttons">
                                    {{-- Tombol Lihat Detail (untuk semua role) --}}
                                    <a href="{{ route('sale.show', $sale->id_penjualan) }}" class="btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- Tombol Hapus (HANYA untuk Admin) --}}
                                    @if(Auth::check() && Auth::user()->role === 'admin')
                                    <form action="{{ route('sale.destroy', $sale->id_penjualan) }}" method="POST" style="display: inline-block;" onsubmit="return confirmDelete(event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" title="Hapus Transaksi">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Tidak ada transaksi penjualan ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($sales) && $sales instanceof \Illuminate\Pagination\LengthAwarePaginator && $sales->hasPages())
            <div class="pagination-wrapper">
                {{ $sales->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>

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

    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: '{{ session('error') }}',
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

        // Function untuk print receipt
        function printReceipt(idPenjualan) {
            // Buka halaman print dalam window baru
            const printUrl = '{{ route("sale.print", ":id") }}'.replace(':id', idPenjualan);
            window.open(printUrl, '_blank', 'width=800,height=600');
        }

        // Function untuk konfirmasi delete
        function confirmDelete(event) {
            event.preventDefault();
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data transaksi ini akan dihapus permanen dan stok akan dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    event.target.submit();
                }
            });
            
            return false;
        }

        $(document).ready(function() {
            let searchTimeout;
            const searchInput = $('#searchInput');
            const dateFilter = $('#dateFilter');
            const searchLoading = $('#searchLoading');
            const searchIcon = $('#searchIcon');
            const clearButton = $('#clearSearch');
            const searchInfo = $('#searchInfo');
            const tableBody = $('#saleTableBody');
            const filterButton = $('#filterButton');

            // Live Search dengan debounce
            searchInput.on('input', function() {
                const query = $(this).val().trim();
                clearTimeout(searchTimeout);

                // Toggle clear button visibility
                toggleClearButton();

                // Debounce search
                searchTimeout = setTimeout(function() {
                    performSearch(query, dateFilter.val());
                }, 500);
            });

            // Date Filter Change
            dateFilter.on('change', function() {
                const date = $(this).val();
                toggleClearButton();
                performSearch(searchInput.val().trim(), date);
            });

            // Filter Button Click
            filterButton.on('click', function() {
                performSearch(searchInput.val().trim(), dateFilter.val());
            });

            // Clear Button Click
            clearButton.on('click', function() {
                searchInput.val('');
                dateFilter.val('');
                clearButton.hide();
                searchInfo.hide();
                
                // Redirect ke halaman tanpa parameter
                window.location.href = '{{ route("sale.history") }}';
            });

            // Toggle Clear Button
            function toggleClearButton() {
                if (searchInput.val().trim() || dateFilter.val()) {
                    clearButton.show();
                } else {
                    clearButton.hide();
                }
            }

            // Perform Search Function
            function performSearch(query, date) {
                // Show loading indicator
                searchIcon.hide();
                searchLoading.show();
                tableBody.addClass('table-loading');

                $.ajax({
                    url: '{{ route("sale.history") }}',
                    type: 'GET',
                    data: { 
                        q: query,
                        tanggal: date
                    },
                    success: function(response) {
                        try {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(response, 'text/html');
                            const newTableBody = doc.querySelector('#saleTableBody');
                            
                            if (newTableBody) {
                                tableBody.html(newTableBody.innerHTML);
                            } else {
                                console.error('Table body not found in response');
                            }

                            // Update search info
                            updateSearchInfo(query, date);

                        } catch (error) {
                            console.error('Parse error:', error);
                            showErrorAlert();
                        } finally {
                            // Hide loading indicator
                            searchLoading.hide();
                            searchIcon.show();
                            tableBody.removeClass('table-loading');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Search error:', error);
                        searchLoading.hide();
                        searchIcon.show();
                        tableBody.removeClass('table-loading');
                        showErrorAlert();
                    }
                });
            }

            // Update Search Info
            function updateSearchInfo(query, date) {
                if (query || date) {
                    let infoText = '';
                    
                    if (query) {
                        infoText += 'Hasil pencarian: <strong>' + escapeHtml(query) + '</strong>';
                    }
                    
                    if (date) {
                        if (query) infoText += ' | ';
                        try {
                            const formattedDate = new Date(date).toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric'
                            });
                            infoText += 'Tanggal: <strong>' + formattedDate + '</strong>';
                        } catch (e) {
                            infoText += 'Tanggal: <strong>' + date + '</strong>';
                        }
                    }
                    
                    searchInfo.html(infoText);
                    searchInfo.show();
                } else {
                    searchInfo.hide();
                }
            }

            // Escape HTML to prevent XSS
            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }

            // Show Error Alert
            function showErrorAlert() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan saat mencari data!',
                    timer: 2000,
                    showConfirmButton: false
                });
            }

            // Prevent form submission on Enter key
            $('#searchForm').on('submit', function(e) {
                e.preventDefault();
                performSearch(searchInput.val().trim(), dateFilter.val());
                return false;
            });

            // Handle Enter key on search input
            searchInput.on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    performSearch($(this).val().trim(), dateFilter.val());
                }
            });
        });
    </script>
</body>
</html>