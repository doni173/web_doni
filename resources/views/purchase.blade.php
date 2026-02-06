<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Transaksi Pembelian | Sistem Inventory dan Kasir</title>

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
                    <span class="search-icon">
                        <i class="bi bi-search"></i>
                    </span>
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
                    
                    {{-- PERUBAHAN: Hapus input tanggal manual, gunakan waktu otomatis --}}
                    {{-- Tampilkan info waktu transaksi saja untuk user --}}
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>
                                <i class="bi bi-calendar-check"></i> Waktu Transaksi
                            </label>
                            <div class="transaction-time-display">
                                <i class="bi bi-clock"></i>
                                <span id="current-datetime">{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y H:i') }} WIB</span>
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
                <label>Supplier</label>
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
                <label>Jumlah Beli</label>
                <input type="number" id="modal-purchase-qty" class="form-control" value="1" min="1" required>
            </div>

            <div class="form-group">
                <label>Harga Beli</label>
                <input type="number" id="modal-product-price" class="form-control" min="0" required>
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
    timer: 2000,
    showConfirmButton: false
});
</script>
@endif

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ================= GLOBAL VARIABLES ================= 
const modal = document.getElementById('purchaseModal');
const summaryBody = document.getElementById('summary-body');
const totalBiayaEl = document.getElementById('total_biaya');

let purchaseCart = [];
let totalBiaya = 0;

// ================= UPDATE REAL-TIME CLOCK ================= 
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
        timeZone: 'Asia/Jakarta'
    };
    const formattedDate = now.toLocaleDateString('id-ID', options);
    document.getElementById('current-datetime').textContent = formattedDate + ' WIB';
}

// Update waktu setiap detik
setInterval(updateCurrentDateTime, 1000);
updateCurrentDateTime(); // Panggil sekali saat load

// ================= SIDEBAR TOGGLE ================= 
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('active');
}

// ================= SEARCH FUNCTIONALITY ================= 
$(document).ready(function() {
    // Search Product
    $('#searchProduct').on('input', function() {
        const query = $(this).val().toLowerCase().trim();
        $('#productTableBody tr').each(function() {
            const productName = $(this).data('product-search');
            if (productName && productName.includes(query)) {
                $(this).show();
            } else if (productName) {
                $(this).hide();
            }
        });
    });

    // ================= OPEN MODAL ================= 
    $(document).on('click', '.btn-purchase', function() {
        const b = $(this);
        modal.style.display = 'block';

        document.getElementById('modal-product-id').value = b.data('id');
        document.getElementById('modal-product-name').value = b.data('name');
        document.getElementById('modal-current-stock').value = b.data('stock');
        document.getElementById('modal-product-price').value = b.data('price');
        document.getElementById('modal-purchase-qty').value = 1;
        document.getElementById('modal-supplier-id').value = '';
    });

    // ================= CLOSE MODAL ================= 
    $('.close, .close-modal').on('click', function() {
        modal.style.display = 'none';
    });

    // Close modal ketika click di luar modal
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // ================= ADD TO CART ================= 
    $('#add-to-summary-btn').on('click', function() {
        const supplierId = document.getElementById('modal-supplier-id').value;
        const qty = document.getElementById('modal-purchase-qty').value;
        const price = document.getElementById('modal-product-price').value;

        // Validasi input
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
        if (!price || price <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Harga beli harus lebih dari 0!',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        const item = {
            id: document.getElementById('modal-product-id').value,
            nama: document.getElementById('modal-product-name').value,
            supplier_id: supplierId,
            stok_lama: parseInt(document.getElementById('modal-current-stock').value),
            jumlah_beli: parseInt(qty),
            harga: parseInt(price),
        };

        purchaseCart.push(item);
        renderPurchaseCart();
        modal.style.display = 'none';

        Swal.fire({
            icon: 'success',
            title: 'Ditambahkan',
            text: `${item.nama} ditambahkan ke keranjang`,
            timer: 1000,
            showConfirmButton: false
        });
    });
});

// ================= RENDER CART ================= 
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
            <td data-label="Stok Lama">${item.stok_lama}</td>
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
                <strong>${item.stok_lama + item.jumlah_beli}</strong>
            </td>
            <td data-label="Harga">Rp ${item.harga.toLocaleString('id-ID')}</td>
            <td data-label="Total">
                <strong>Rp ${total.toLocaleString('id-ID')}</strong>
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

// ================= UPDATE SUMMARY ================= 
function updateSummary() {
    totalBiayaEl.innerText = 'Rp ' + totalBiaya.toLocaleString('id-ID');
}

// ================= QUANTITY CONTROLS ================= 
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
    
    if (qty < 1) {
        removeFromPurchaseCart(index);
        return;
    }
    
    purchaseCart[index].jumlah_beli = qty;
    renderPurchaseCart();
}

// ================= REMOVE FROM CART ================= 
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

// ================= CLEAR CART ================= 
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

// ================= SUBMIT FORM ================= 
document.getElementById('purchase-form').addEventListener('submit', function (e) {
    e.preventDefault();

    // Validasi cart tidak kosong
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

    // PERUBAHAN: Tidak perlu validasi tanggal karena menggunakan waktu otomatis
    // Waktu akan di-set di backend menggunakan Carbon::now('Asia/Jakarta')

    // Konfirmasi sebelum submit
    const currentTime = new Date().toLocaleString('id-ID', { 
        timeZone: 'Asia/Jakarta',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    Swal.fire({
        title: 'Konfirmasi Pembelian',
        html: `
            <div style="text-align: left; padding: 10px;">
                <p><strong>Waktu Transaksi:</strong> ${currentTime} WIB</p>
                <p><strong>Total Pembelian:</strong> Rp ${totalBiaya.toLocaleString('id-ID')}</p>
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

            // PERUBAHAN: Tidak kirim tanggal_pembelian, akan di-set otomatis di backend
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
                    // tanggal_pembelian TIDAK dikirim, akan otomatis di backend
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
                            <p><strong>Total Pembelian:</strong> Rp ${totalBiaya.toLocaleString('id-ID')}</p>
                            <p>Data pembelian berhasil disimpan</p>
                        </div>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    // Reset form dan cart
                    purchaseCart = [];
                    renderPurchaseCart();
                    
                    // Redirect atau reload halaman
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: response.message || 'Gagal menyimpan pembelian',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
    });
});
</script>

<style>
/* Style untuk display waktu transaksi */
.transaction-time-display {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-left: 4px solid #3b82f6;
    border-radius: 8px;
    font-weight: 600;
    color: #1e40af;
    margin-top: 8px;
}

.transaction-time-display i {
    font-size: 20px;
    color: #3b82f6;
}

.text-muted {
    color: #6b7280;
    font-size: 12px;
    display: block;
    margin-top: 8px;
}
</style>

</body>
</html>