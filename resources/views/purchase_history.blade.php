<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Pembelian | Sistem Inventory dan Kasir</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
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
            <h2>History Pembelian</h2>
            
            <div class="buttons">
                <form action="{{ route('purchase.history') }}" method="GET" class="search-form" id="searchForm">
                    <div class="search-input-wrapper">
                        <input 
                            type="text" 
                            class="form-control" 
                            placeholder="Cari ID pembelian atau supplier..." 
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
                            <th>ID Pembelian</th>
                            <th>Tanggal & Waktu</th>
                            <th>Supplier</th>
                            <th>Total Pembelian</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="purchaseTableBody">
                        @forelse($purchases as $purchase)
                        <tr>
                            <td data-label="ID Pembelian">
                                <span class="id-badge">{{ $purchase->id_pembelian }}</span>
                            </td>
                            <td data-label="Tanggal & Waktu">
                                {{ \Carbon\Carbon::parse($purchase->tgl_pembelian)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                            </td>
                            <td data-label="Supplier">
                                <span class="product-name">{{ $purchase->supplier->nama_supplier ?? '-' }}</span>
                            </td>
                            <td data-label="Total Pembelian">
                                <span class="stock-number">Rp {{ number_format($purchase->total_pembelian, 0, ',', '.') }}</span>
                            </td>
                            <td data-label="Aksi">
                                <div class="action-buttons">
                                    <a href="{{ route('purchase.show', $purchase->id_pembelian) }}" class="btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(Auth::check() && Auth::user()->role === 'admin')
                                    <form action="{{ route('purchase.destroy', $purchase->id_pembelian) }}" method="POST" style="display: inline-block;" onsubmit="return confirmDelete(event, this)">
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
                            <td colspan="5" class="text-center">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Tidak ada transaksi pembelian ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($purchases) && $purchases instanceof \Illuminate\Pagination\LengthAwarePaginator && $purchases->hasPages())
            <div class="pagination-wrapper">
                {{ $purchases->appends(request()->query())->links() }}
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

        function confirmDelete(event, form) {
            event.preventDefault();
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data transaksi pembelian ini akan dihapus permanen dan stok akan dikurangi kembali!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
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
            const tableBody = $('#purchaseTableBody');
            const filterButton = $('#filterButton');

            function toggleClearButton() {
                if (searchInput.val().trim() || dateFilter.val()) {
                    clearButton.show();
                } else {
                    clearButton.hide();
                }
            }

            searchInput.on('input', function() {
                const query = $(this).val().trim();
                clearTimeout(searchTimeout);

                toggleClearButton();

                searchTimeout = setTimeout(function() {
                    performSearch(query, dateFilter.val());
                }, 500);
            });

            dateFilter.on('change', function() {
                const date = $(this).val();
                toggleClearButton();
                performSearch(searchInput.val().trim(), date);
            });

            filterButton.on('click', function() {
                performSearch(searchInput.val().trim(), dateFilter.val());
            });

            clearButton.on('click', function() {
                searchInput.val('');
                dateFilter.val('');
                clearButton.hide();
                searchInfo.hide();
                
                window.location.href = '{{ route("purchase.history") }}';
            });

            function performSearch(query, date) {
                searchIcon.hide();
                searchLoading.show();
                tableBody.addClass('table-loading');

                $.ajax({
                    url: '{{ route("purchase.history") }}',
                    type: 'GET',
                    data: { 
                        q: query,
                        tanggal: date
                    },
                    success: function(response) {
                        try {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(response, 'text/html');
                            const newTableBody = doc.querySelector('#purchaseTableBody');
                            
                            if (newTableBody) {
                                tableBody.html(newTableBody.innerHTML);
                            } else {
                                console.error('Table body not found in response');
                            }

                            updateSearchInfo(query, date);

                        } catch (error) {
                            console.error('Parse error:', error);
                            showErrorAlert();
                        } finally {
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

            function showErrorAlert() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan saat mencari data!',
                    timer: 2000,
                    showConfirmButton: false
                });
            }

            $('#searchForm').on('submit', function(e) {
                e.preventDefault();
                performSearch(searchInput.val().trim(), dateFilter.val());
                return false;
            });

            searchInput.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    performSearch($(this).val().trim(), dateFilter.val());
                }
            });
        });
    </script>
</body>
</html>