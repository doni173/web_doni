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

    <style>
        /* ================= ACTION BUTTONS FIX ================= */
        .action-buttons {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
        }

        .action-buttons form {
            display: inline-flex !important;
            margin: 0;
            padding: 0;
        }

        /* ================= PAGINATION STYLES ================= */
        .pagination-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 4px 4px 4px;
            flex-wrap: wrap;
            gap: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            color: #374151;
        }

        .pagination-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pagination-left label {
            font-weight: 500;
            color: #374151;
            white-space: nowrap;
        }

        .pagination-per-page {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 4px 8px;
            background: #fff;
            cursor: pointer;
            font-size: 13px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #374151;
            font-weight: 500;
            outline: none;
            transition: border-color 0.2s;
        }

        .pagination-per-page:hover,
        .pagination-per-page:focus {
            border-color: #06b6d4;
        }

        .pagination-center {
            color: #6b7280;
            font-size: 13px;
        }

        .pagination-nav {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .pagination-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #d1d5db;
            background: #fff;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            transition: all 0.18s;
            outline: none;
            user-select: none;
        }

        .pagination-btn:hover:not(:disabled):not(.active) {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        .pagination-btn.active {
            background: #06b6d4;
            border-color: #06b6d4;
            color: #fff;
            font-weight: 700;
        }

        .pagination-btn:disabled {
            opacity: 0.38;
            cursor: not-allowed;
        }

        .pagination-btn i {
            font-size: 12px;
        }
    </style>
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
                        <tr data-purchase-row>
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
                                    <form action="{{ route('purchase.destroy', $purchase->id_pembelian) }}" method="POST" onsubmit="return confirmDelete(event, this)">
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

            <!-- ================= PAGINATION ================= -->
            <div class="pagination-wrapper" id="purchasePaginationWrapper">
                <div class="pagination-left">
                    <label for="purchasePerPage">Tampilkan:</label>
                    <select class="pagination-per-page" id="purchasePerPage" onchange="changePurchasePerPage(this.value)">
                        <option value="10">10 baris</option>
                        <option value="20" selected>20 baris</option>
                        <option value="50">50 baris</option>
                        <option value="100">100 baris</option>
                    </select>
                </div>
                <div class="pagination-center" id="purchasePaginationInfo"></div>
                <div class="pagination-nav" id="purchasePaginationNav"></div>
            </div>
            <!-- ================= END PAGINATION ================= -->

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
        // ================= SIDEBAR =================
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }

        // ================= DELETE CONFIRM =================
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

        // ================= PAGINATION STATE =================
        const purchasePagination = {
            currentPage: 1,
            perPage: 20,
            filteredRows: []
        };

        // ================= PAGINATION HELPERS =================
        function renderPagination(state, infoId, navId, goToPage) {
            const total = state.filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(total / state.perPage));
            const start = total === 0 ? 0 : (state.currentPage - 1) * state.perPage + 1;
            const end   = Math.min(state.currentPage * state.perPage, total);

            document.getElementById(infoId).textContent =
                `Menampilkan ${start} - ${end} dari ${total} data`;

            const nav = document.getElementById(navId);
            nav.innerHTML = '';

            nav.appendChild(createPagBtn('<i class="bi bi-chevron-double-left"></i>', state.currentPage === 1, () => goToPage(1)));
            nav.appendChild(createPagBtn('<i class="bi bi-chevron-left"></i>', state.currentPage === 1, () => goToPage(state.currentPage - 1)));

            pageRange(state.currentPage, totalPages, 5).forEach(p => {
                const btn = createPagBtn(p, false, () => goToPage(p));
                if (p === state.currentPage) btn.classList.add('active');
                nav.appendChild(btn);
            });

            nav.appendChild(createPagBtn('<i class="bi bi-chevron-right"></i>', state.currentPage === totalPages, () => goToPage(state.currentPage + 1)));
            nav.appendChild(createPagBtn('<i class="bi bi-chevron-double-right"></i>', state.currentPage === totalPages, () => goToPage(totalPages)));
        }

        function createPagBtn(html, disabled, onClick) {
            const btn = document.createElement('button');
            btn.className = 'pagination-btn';
            btn.innerHTML = html;
            btn.disabled = disabled;
            if (!disabled) btn.addEventListener('click', onClick);
            return btn;
        }

        function pageRange(current, total, maxVisible) {
            if (total <= maxVisible) {
                return Array.from({ length: total }, (_, i) => i + 1);
            }
            let start = Math.max(1, current - Math.floor(maxVisible / 2));
            let end = start + maxVisible - 1;
            if (end > total) {
                end = total;
                start = Math.max(1, end - maxVisible + 1);
            }
            return Array.from({ length: end - start + 1 }, (_, i) => start + i);
        }

        function applyPaginationVisibility(state) {
            const start = (state.currentPage - 1) * state.perPage;
            const end   = start + state.perPage;
            state.filteredRows.forEach((row, i) => {
                row.style.display = (i >= start && i < end) ? '' : 'none';
            });
        }

        // ================= PURCHASE TABLE PAGINATION =================
        function initPurchasePagination() {
            const allRows = Array.from(document.querySelectorAll('#purchaseTableBody tr[data-purchase-row]'));
            purchasePagination.filteredRows = allRows;
            purchasePagination.currentPage  = 1;
            applyPaginationVisibility(purchasePagination);
            renderPagination(purchasePagination, 'purchasePaginationInfo', 'purchasePaginationNav', goToPurchasePage);
        }

        function goToPurchasePage(page) {
            const totalPages = Math.max(1, Math.ceil(purchasePagination.filteredRows.length / purchasePagination.perPage));
            purchasePagination.currentPage = Math.min(Math.max(1, page), totalPages);
            applyPaginationVisibility(purchasePagination);
            renderPagination(purchasePagination, 'purchasePaginationInfo', 'purchasePaginationNav', goToPurchasePage);
        }

        function changePurchasePerPage(val) {
            purchasePagination.perPage = parseInt(val);
            goToPurchasePage(1);
        }

        function reinitPaginationAfterSearch() {
            initPurchasePagination();
        }

        $(document).ready(function() {

            // Init pagination on load
            initPurchasePagination();

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

                            // Re-init pagination after AJAX updates rows
                            reinitPaginationAfterSearch();

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