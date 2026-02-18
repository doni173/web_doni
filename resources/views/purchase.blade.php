<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Transaksi Pembelian | Sistem Inventory dan Kasir</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ================= PAGINATION STYLES ================= */
        .pagination-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 4px 4px 4px;
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
            display: flex;
            align-items: center;
            gap: 6px;
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

        <h2>Transaksi Pembelian</h2>

        <!-- ================= DAFTAR PRODUK SECTION ================= -->
        <div class="section-wrapper">
            <h3 class="section-title">
                <i class="bi bi-box-seam"></i> Daftar Produk
            </h3>
            
            <div class="search-wrapper">
                <div class="search-input-wrapper">
                    <input type="text" 
                           class="form-control" 
                           placeholder="Cari produk..." 
                           id="searchProduct"
                           autocomplete="off">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-main">
                    <thead>
                        <tr>
                            <th>ID Produk</th>
                            <th>Nama Produk</th>
                            <th>Stok</th>
                            <th>Harga Beli</th>
                            <th>Supplier</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        @forelse ($items as $item)
                        <tr data-product-search="{{ strtolower($item->nama_produk) }}">
                            <td data-label="ID Produk">
                                <span class="id-badge">{{ $item->id_produk }}</span>
                            </td>
                            <td data-label="Nama Produk">
                                <span class="product-name">{{ $item->nama_produk }}</span>
                            </td>
                            <td data-label="Stok">
                                <span class="badge {{ $item->stok == 0 ? 'badge-danger' : ($item->stok < 10 ? 'badge-warning' : 'badge-success') }}">
                                    {{ $item->stok }} unit
                                </span>
                            </td>
                            <td data-label="Harga Beli">
                                Rp {{ number_format($item->modal, 0, ',', '.') }}
                            </td>
                            <td data-label="Supplier">
                                {{ $item->supplier->nama_supplier ?? '-' }}
                            </td>
                            <td data-label="Aksi">
                                <button class="btn-add-cart btn-purchase"
                                        data-id="{{ $item->id_produk }}"
                                        data-name="{{ $item->nama_produk }}"
                                        data-stock="{{ $item->stok }}"
                                        data-price="{{ $item->modal }}">
                                    <i class="bi bi-plus-circle"></i>
                                    Tambah
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Tidak ada data produk</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- ================= PRODUCT PAGINATION ================= -->
            <div class="pagination-wrapper" id="productPaginationWrapper">
                <div class="pagination-left">
                    <label for="productPerPage">Tampilkan:</label>
                    <select class="pagination-per-page" id="productPerPage" onchange="changeProductPerPage(this.value)">
                        <option value="10">10 baris</option>
                        <option value="20" selected>20 baris</option>
                        <option value="50">50 baris</option>
                        <option value="100">100 baris</option>
                    </select>
                </div>
                <div class="pagination-center" id="productPaginationInfo"></div>
                <div class="pagination-nav" id="productPaginationNav"></div>
            </div>
            <!-- ================= END PRODUCT PAGINATION ================= -->
        </div>
        <br>

        <!-- ================= RINGKASAN PEMBELIAN SECTION ================= -->
        <div class="section-wrapper cart-section">
            <h3 class="section-title">
                <i class="bi bi-cart-check"></i> Ringkasan Pembelian
            </h3>

            <div class="table-responsive">
                <table class="table-main">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Stok Lama</th>
                            <th>Jumlah Beli</th>
                            <th>Stok Baru</th>
                            <th>Harga</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="summary-body">
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <i class="bi bi-cart-x"></i>
                                    <p>Belum ada produk yang ditambahkan</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ================= CHECKOUT FORM ================= -->
            <div class="checkout-section">
                <form id="purchase-form">
                    @csrf
                    
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>
                                <i class="bi bi-calendar-check"></i> Waktu Transaksi
                            </label>
                            <div class="transaction-time-display">
                                <i class="bi bi-clock"></i>
                                <span id="current-datetime">{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y H:i:s') }} WIB</span>
                            </div>
                            <small class="text-muted">Waktu transaksi akan tercatat secara otomatis saat Anda menyelesaikan pembelian</small>
                        </div>
                    </div>

                    <div class="summary-section">
                        <div class="summary-item">
                            <span class="summary-label">Total Pembelian:</span>
                            <span class="summary-value" id="total_biaya">Rp 0</span>
                        </div>
                    </div>

                    <div class="checkout-actions">
                        <button type="button" class="btn-clear-cart" onclick="clearPurchaseCart()">
                            <i class="bi bi-trash"></i> Kosongkan Keranjang
                        </button>
                        <button type="submit" class="btn-checkout" id="btnCheckout" disabled>
                            <i class="bi bi-check-circle"></i> Selesaikan Pembelian
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- ================= MODAL PEMBELIAN ================= -->
<div id="purchaseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <i class="bi bi-cart-plus"></i>
                <h5>Tambah Pembelian</h5>
            </div>
            <button class="close" type="button">&times;</button>
        </div>
        
        <div class="modal-body">
            <input type="hidden" id="modal-product-id">

            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" id="modal-product-name" class="form-control" readonly>
            </div>

            <div class="form-group">
                <label>Supplier <span style="color: #ef4444;">*</span></label>
                <select id="modal-supplier-id" class="form-control" required>
                    <option value="">-- Pilih Supplier --</option>
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->id_supplier }}">{{ $s->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Stok Saat Ini</label>
                <input type="number" id="modal-current-stock" class="form-control" readonly>
            </div>

            <div class="form-group">
                <label>Jumlah Beli <span style="color: #ef4444;">*</span></label>
                <input type="number" id="modal-purchase-qty" class="form-control" value="1" min="1" required>
            </div>

            <div class="form-group">
                <label>Harga Beli (per unit) <span style="color: #ef4444;">*</span></label>
                {{-- ✅ Ganti type="number" menjadi type="text" agar bisa diformat --}}
                <input type="text" id="modal-product-price" class="form-control" required>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary close-modal">Batal</button>
                <button type="button" id="add-to-summary-btn" class="btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambahkan
                </button>
            </div>
        </div>
    </div>
</div>

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
    timer: 2000,
    showConfirmButton: false
});
</script>
@endif

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border-radius: var(--radius-lg);
    width: 90%;
    max-width: 500px;
    box-shadow: var(--shadow-xl);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 2px solid var(--gray-200);
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}

.modal-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-title h5 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
}

.modal-title i {
    font-size: 22px;
}

.close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 28px;
    font-weight: 700;
    cursor: pointer;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-base);
}

.close:hover,
.close:focus {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.modal-body {
    padding: 24px;
}

.modal-body .form-group {
    margin-bottom: 20px;
}

.modal-body .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-primary);
    font-size: 14px;
}

.modal-body .form-control {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-sm);
    font-size: 14px;
    transition: var(--transition-base);
}

.modal-body .form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
}

.modal-body .form-control[readonly] {
    background-color: var(--gray-100);
    cursor: not-allowed;
}

.modal-footer-custom {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 20px;
    border-top: 1px solid var(--gray-200);
    margin-top: 20px;
}

.transaction-time-display {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-left: 4px solid var(--primary-color);
    border-radius: var(--radius-sm);
    font-weight: 600;
    color: #1e40af;
    margin-top: 8px;
    box-shadow: 0 2px 8px rgba(14, 165, 233, 0.1);
}

.transaction-time-display i {
    font-size: 20px;
    color: var(--primary-color);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

@media (max-width: 768px) {
    .modal-content {
        margin: 10% auto;
        width: 95%;
    }

    .modal-header {
        padding: 16px 20px;
    }

    .modal-title h5 {
        font-size: 16px;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer-custom {
        flex-direction: column;
    }

    .btn-secondary,
    .btn-primary {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
const modal = document.getElementById('purchaseModal');
const summaryBody = document.getElementById('summary-body');
const totalBiayaEl = document.getElementById('total_biaya');

let purchaseCart = [];
let totalBiaya = 0;

// ================= PAGINATION STATE =================
const productPagination = {
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

// ================= PRODUCT PAGINATION =================

function initProductPagination() {
    const allRows = Array.from(document.querySelectorAll('#productTableBody tr[data-product-search]'));
    productPagination.filteredRows = allRows;
    productPagination.currentPage  = 1;
    applyPaginationVisibility(productPagination);
    renderPagination(productPagination, 'productPaginationInfo', 'productPaginationNav', goToProductPage);
}

function goToProductPage(page) {
    const totalPages = Math.max(1, Math.ceil(productPagination.filteredRows.length / productPagination.perPage));
    productPagination.currentPage = Math.min(Math.max(1, page), totalPages);
    applyPaginationVisibility(productPagination);
    renderPagination(productPagination, 'productPaginationInfo', 'productPaginationNav', goToProductPage);
}

function changeProductPerPage(val) {
    productPagination.perPage = parseInt(val);
    goToProductPage(1);
}

// ================= FORMAT RUPIAH =================
function formatRupiah(angka) {
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// ================= DATE TIME =================

function updateCurrentDateTime() {
    const now = new Date();
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Asia/Jakarta',
        hour12: false
    };
    const formattedDate = now.toLocaleDateString('id-ID', options);
    document.getElementById('current-datetime').textContent = formattedDate + ' WIB';
}

setInterval(updateCurrentDateTime, 1000);
updateCurrentDateTime();

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('active');
}

$(document).ready(function() {

    // Init pagination on load
    initProductPagination();

    // Search Product — refilter then repaginate
    $('#searchProduct').on('input', function() {
        const query = $(this).val().toLowerCase().trim();
        const allRows = Array.from(document.querySelectorAll('#productTableBody tr[data-product-search]'));

        if (query === '') {
            productPagination.filteredRows = allRows;
        } else {
            productPagination.filteredRows = allRows.filter(row =>
                row.getAttribute('data-product-search').includes(query)
            );
            allRows.forEach(r => r.style.display = 'none');
        }

        goToProductPage(1);
    });

    $(document).on('click', '.btn-purchase', function() {
        const b = $(this);
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';

        document.getElementById('modal-product-id').value = b.data('id');
        document.getElementById('modal-product-name').value = b.data('name');
        document.getElementById('modal-current-stock').value = b.data('stock');

        // ✅ Tampilkan harga dengan format titik: 70.000
        document.getElementById('modal-product-price').value = formatRupiah(b.data('price').toString());

        document.getElementById('modal-purchase-qty').value = 1;
        document.getElementById('modal-supplier-id').value = '';
    });

    // ✅ Format harga saat user mengetik di input harga
    $('#modal-product-price').on('input', function() {
        let raw = this.value.replace(/\./g, '').replace(/\D/g, '');
        this.value = raw ? formatRupiah(raw) : '';
    });

    $('.close, .close-modal').on('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    });

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
            document.body.style.overflow = '';
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    });

    $('#add-to-summary-btn').on('click', function() {
        const supplierId = document.getElementById('modal-supplier-id').value;
        const qty        = document.getElementById('modal-purchase-qty').value;

        // ✅ Hapus titik sebelum dihitung agar jadi angka asli
        const priceRaw   = document.getElementById('modal-product-price').value.replace(/\./g, '');
        const productId  = document.getElementById('modal-product-id').value;

        if (!supplierId) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pilih supplier terlebih dahulu!',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        if (!qty || qty <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Jumlah beli harus lebih dari 0!',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        if (!priceRaw || parseInt(priceRaw) < 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Harga beli tidak valid!',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        const existingIndex = purchaseCart.findIndex(item => item.id === productId);
        
        if (existingIndex !== -1) {
            purchaseCart[existingIndex].jumlah_beli = parseInt(purchaseCart[existingIndex].jumlah_beli) + parseInt(qty);
        } else {
            const item = {
                id: productId,
                nama: document.getElementById('modal-product-name').value,
                supplier_id: supplierId,
                stok_lama: parseInt(document.getElementById('modal-current-stock').value),
                jumlah_beli: parseInt(qty),
                harga: parseInt(priceRaw), // ✅ simpan angka asli tanpa titik
            };
            purchaseCart.push(item);
        }

        renderPurchaseCart();
        modal.style.display = 'none';
        document.body.style.overflow = '';

        Swal.fire({
            icon: 'success',
            title: 'Ditambahkan',
            text: `${document.getElementById('modal-product-name').value} ditambahkan ke keranjang`,
            timer: 1000,
            showConfirmButton: false
        });
    });
});

function renderPurchaseCart() {
    summaryBody.innerHTML = '';
    totalBiaya = 0;

    if (purchaseCart.length === 0) {
        summaryBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center">
                    <div class="empty-state">
                        <i class="bi bi-cart-x"></i>
                        <p>Belum ada produk yang ditambahkan</p>
                    </div>
                </td>
            </tr>
        `;
        $('#btnCheckout').prop('disabled', true);
        updateSummary();
        return;
    }

    purchaseCart.forEach((item, idx) => {
        const total = item.jumlah_beli * item.harga;
        totalBiaya += total;

        summaryBody.innerHTML += `
        <tr>
            <td data-label="ID">
                <span class="id-badge">${item.id}</span>
            </td>
            <td data-label="Nama">
                <span class="product-name">${item.nama}</span>
            </td>
            <td data-label="Stok Lama">
                <span class="stock-number">${item.stok_lama}</span>
            </td>
            <td data-label="Jumlah Beli">
                <div class="quantity-control">
                    <button type="button" class="btn-qty" onclick="decreasePurchaseQty(${idx})">
                        <i class="bi bi-dash"></i>
                    </button>
                    <input type="number" 
                           class="qty-input" 
                           value="${item.jumlah_beli}" 
                           min="1"
                           onchange="updatePurchaseQty(${idx}, this.value)">
                    <button type="button" class="btn-qty" onclick="increasePurchaseQty(${idx})">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
            </td>
            <td data-label="Stok Baru">
                <strong style="color: #10b981;">${item.stok_lama + item.jumlah_beli}</strong>
            </td>
            <td data-label="Harga">Rp ${formatRupiah(item.harga.toString())}</td>
            <td data-label="Total">
                <strong>Rp ${formatRupiah(total.toString())}</strong>
            </td>
            <td data-label="Aksi">
                <button type="button" class="btn-delete" onclick="removeFromPurchaseCart(${idx})" title="Hapus">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>`;
    });

    updateSummary();
    $('#btnCheckout').prop('disabled', false);
}

function updateSummary() {
    totalBiayaEl.innerText = 'Rp ' + formatRupiah(totalBiaya.toString());
}

function increasePurchaseQty(index) {
    purchaseCart[index].jumlah_beli++;
    renderPurchaseCart();
}

function decreasePurchaseQty(index) {
    if (purchaseCart[index].jumlah_beli > 1) {
        purchaseCart[index].jumlah_beli--;
        renderPurchaseCart();
    } else {
        removeFromPurchaseCart(index);
    }
}

function updatePurchaseQty(index, value) {
    const qty = parseInt(value);
    
    if (qty < 1 || isNaN(qty)) {
        removeFromPurchaseCart(index);
        return;
    }
    
    purchaseCart[index].jumlah_beli = qty;
    renderPurchaseCart();
}

function removeFromPurchaseCart(index) {
    Swal.fire({
        title: 'Hapus Item?',
        text: 'Apakah Anda yakin ingin menghapus item ini dari keranjang?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            purchaseCart.splice(index, 1);
            renderPurchaseCart();
            Swal.fire({
                icon: 'success',
                title: 'Dihapus',
                text: 'Item berhasil dihapus dari keranjang',
                timer: 1000,
                showConfirmButton: false
            });
        }
    });
}

function clearPurchaseCart() {
    if (purchaseCart.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Keranjang Kosong',
            text: 'Tidak ada item di keranjang',
            timer: 2000,
            showConfirmButton: false
        });
        return;
    }

    Swal.fire({
        title: 'Kosongkan Keranjang?',
        text: 'Semua item akan dihapus dari keranjang!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-trash"></i> Ya, Kosongkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            purchaseCart = [];
            renderPurchaseCart();
            Swal.fire({
                icon: 'success',
                title: 'Dikosongkan',
                text: 'Keranjang berhasil dikosongkan',
                timer: 1000,
                showConfirmButton: false
            });
        }
    });
}

document.getElementById('purchase-form').addEventListener('submit', function (e) {
    e.preventDefault();

    if (purchaseCart.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Keranjang Kosong',
            text: 'Silakan tambahkan produk terlebih dahulu!',
            timer: 2000,
            showConfirmButton: false
        });
        return;
    }

    const currentTime = new Date().toLocaleString('id-ID', { 
        timeZone: 'Asia/Jakarta',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    Swal.fire({
        title: 'Konfirmasi Pembelian',
        html: `
            <div style="text-align: left; padding: 10px;">
                <p><strong>Waktu Transaksi:</strong> ${currentTime} WIB</p>
                <p><strong>Total Pembelian:</strong> Rp ${formatRupiah(totalBiaya.toString())}</p>
                <p><strong>Jumlah Item:</strong> ${purchaseCart.length} produk</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-check-circle"></i> Ya, Proses!',
        cancelButtonText: 'Batal',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const submitBtn = document.querySelector('#btnCheckout');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';

            return fetch("{{ route('purchase.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    total_pembelian: totalBiaya,
                    items: purchaseCart
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Terjadi kesalahan pada server');
                    });
                }
                return response.json();
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Selesaikan Pembelian';
                Swal.showValidationMessage(`Request failed: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            const response = result.value;
            
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Pembelian Berhasil!',
                    html: `
                        <div style="text-align: left; padding: 10px;">
                            <p><strong>ID Pembelian:</strong> ${response.data?.id_pembelian || '-'}</p>
                            <p><strong>Total Pembelian:</strong> Rp ${formatRupiah(totalBiaya.toString())}</p>
                            <p>Data pembelian berhasil disimpan</p>
                        </div>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    purchaseCart = [];
                    renderPurchaseCart();
                    // ✅ Reload halaman pembelian, tidak diarahkan ke history
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: response.message || 'Gagal menyimpan pembelian',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                const submitBtn = document.querySelector('#btnCheckout');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Selesaikan Pembelian';
            }
        } else {
            const submitBtn = document.querySelector('#btnCheckout');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Selesaikan Pembelian';
        }
    });
});
</script>

</body>
</html>