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
                            <td data-label="Kasir">{{ $sale->id_user }}</td>
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
                                    <a href="{{ route('sale.show', $sale->id_penjualan) }}" class="btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Tidak ada transaksi penjualan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($sales) && $sales instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="pagination-wrapper">
                {{ $sales->links() }}
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
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

            // Live Search
            searchInput.on('input', function() {
                const query = $(this).val().trim();
                clearTimeout(searchTimeout);

                if (query.length > 0 || dateFilter.val()) {
                    clearButton.show();
                } else {
                    clearButton.hide();
                }

                searchTimeout = setTimeout(function() {
                    performSearch(query, dateFilter.val());
                }, 500);
            });

            // Date Filter Change
            dateFilter.on('change', function() {
                const date = $(this).val();
                if (date || searchInput.val().trim()) {
                    clearButton.show();
                }
                performSearch(searchInput.val().trim(), date);
            });

            // Filter Button Click
            filterButton.on('click', function() {
                performSearch(searchInput.val().trim(), dateFilter.val());
            });

            // Clear Button
            clearButton.on('click', function() {
                searchInput.val('');
                dateFilter.val('');
                clearButton.hide();
                searchInfo.hide();
                performSearch('', '');
            });

            function performSearch(query, date) {
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
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(response, 'text/html');
                        const newTableBody = doc.querySelector('#saleTableBody');
                        
                        if (newTableBody) {
                            tableBody.html(newTableBody.innerHTML);
                        }

                        // Update search info
                        if (query || date) {
                            let infoText = '';
                            if (query) {
                                infoText += 'Hasil pencarian: <strong>' + query + '</strong>';
                            }
                            if (date) {
                                if (query) infoText += ' | ';
                                const formattedDate = new Date(date).toLocaleDateString('id-ID', {
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric'
                                });
                                infoText += 'Tanggal: <strong>' + formattedDate + '</strong>';
                            }
                            searchInfo.html(infoText);
                            searchInfo.show();
                        } else {
                            searchInfo.hide();
                        }

                        searchLoading.hide();
                        searchIcon.show();
                        tableBody.removeClass('table-loading');
                    },
                    error: function(xhr, status, error) {
                        console.error('Search error:', error);
                        searchLoading.hide();
                        searchIcon.show();
                        tableBody.removeClass('table-loading');
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
</body>
</html>