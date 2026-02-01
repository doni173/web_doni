<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pembelian Barang | Sistem Inventory dan Kasir</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
@include('layouts.sidebar')
@include('layouts.navbar')

<div class="container">
    <div class="container-daftar">
        <div class="padding">
            <h2>Pembelian Barang</h2>
        </div>

        <!-- Form Pencarian -->
        <div class="search-section">
            <form action="{{ route('purchase.index') }}" method="GET" class="search-form">
                <input type="text" class="form-control" placeholder="Cari produk..." name="q" value="{{ request('q') }}" style="width: 300px;">
                <button type="submit" class="btn-src1">Search</button>
            </form>
        </div>

        <!-- Tabel Daftar Produk -->
        <div class="table-container">
            <table class="table-main">
                <thead>
                    <tr>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Stok Saat Ini</th>
                        <th>Harga Beli</th>
                        <th>Supplier</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr class="{{ $item->stok < 10 ? 'low-stock' : '' }}">
                        <td>{{ $item->id_produk }}</td>
                        <td>{{ $item->nama_produk }}</td>
                        <td>
                            <span class="stock-badge {{ $item->stok < 10 ? 'badge-danger' : 'badge-success' }}">
                                {{ $item->stok }}
                            </span>
                        </td>
                        <td>Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                        <td>{{ $item->supplier ?? '-' }}</td>
                        <td>
                            <button type="button" class="btn btn-primary btn-purchase" 
                                data-id="{{ $item->id_produk }}"
                                data-name="{{ $item->nama_produk }}"
                                data-stock="{{ $item->stok }}"
                                data-price="{{ $item->harga_beli }}">
                                Tambah
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">Tidak ada data produk</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            {{ $items->links() }}
        </div>
    </div>

    <!-- Ringkasan Pembelian -->
    <div class="container-ringkasan">
        <h3>Ringkasan Pembelian</h3>
        <table class="table-main" id="summary-table">
            <thead>
                <tr>
                    <th>Kode Produk</th>
                    <th>Nama Produk</th>
                    <th>Stok Lama</th>
                    <th>Jumlah Beli</th>
                    <th>Stok Baru</th>
                    <th>Harga Beli Satuan</th>
                    <th>Total Biaya</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="summary-body">
                <!-- Data pembelian akan muncul di sini -->
            </tbody>
        </table>
    </div>

    <!-- Form Penyelesaian Pembelian -->
    <div class="container-bayar">
        <form id="purchase-form">
            @csrf
            <div class="form-wrapper">     
                <div class="form-group">
                    <label for="tanggal_pembelian">Tanggal Pembelian</label>
                    <input type="date" id="tanggal_pembelian" name="tanggal_pembelian" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label for="supplier">Supplier</label>
                    <input type="text" id="supplier" name="supplier" class="form-control" placeholder="Nama Supplier" required>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <input type="text" id="keterangan" name="keterangan" class="form-control" placeholder="Keterangan (opsional)">
                </div>
            
                <div class="form-group">
                    <h4>Total Biaya: <span id="total_biaya">Rp. 0</span></h4>
                </div>
            </div>
            <button type="submit" class="btn add">Selesaikan Pembelian</button>
        </form>
    </div>
</div>

<!-- Modal Purchase -->
<div id="purchaseModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Pembelian Produk</h3>
        <form id="modal-purchase-form">
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" id="modal-product-name" class="form-control" readonly>
                <input type="hidden" id="modal-product-id">
            </div>
            <div class="form-group">
                <label>Stok Saat Ini</label>
                <input type="number" id="modal-current-stock" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label>Jumlah Beli</label>
                <input type="number" id="modal-purchase-qty" class="form-control" min="1" value="1" required>
            </div>
            <div class="form-group">
                <label>Harga Beli Satuan</label>
                <input type="number" id="modal-product-price" class="form-control" min="0" step="1">
            </div>
            <div class="form-group">
                <label>Total Biaya</label>
                <input type="text" id="modal-total-cost" class="form-control" readonly>
            </div>
            <button type="button" id="add-to-summary-btn" class="btn btn-primary">Tambahkan ke Ringkasan</button>
        </form>
    </div>
</div>

@push('styles')
<style>
    body {
        font-family: 'Open Sans', sans-serif;
        background-color: #d6ebff;
        color: #333;
    }

    .container-daftar {
        margin-top: 25px;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .container-daftar h2 {
        margin-top: 1px;
        margin-bottom: 20px;
    }

    .search-section {
        margin-bottom: 20px;
    }

    .search-form {
        display: flex;
        gap: 10px;
    }

    .container-ringkasan {
        margin-top: 15px;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .container-ringkasan h3 {
        margin-top: 1px;
        margin-bottom: 15px;
    }

    .container-bayar {
        font-family: 'Open Sans', sans-serif;
        margin-top: 15px;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .form-wrapper {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .form-group {
        margin-top: 8px;
        margin-left: 10px;
        flex: 1;
        min-width: 200px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .table-container {
        margin-bottom: 20px;
        overflow-x: auto;
    }

    .table-main {
        width: 100%;
        border-collapse: collapse;
    }

    .table-main th, .table-main td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .table-main th {
        background-color: #007bff;
        color: #fff;
        font-weight: 600;
    }

    .table-main tbody tr:hover {
        background-color: #f5f5f5;
    }

    .low-stock {
        background-color: #fff3cd !important;
    }

    .stock-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 14px;
    }

    .badge-danger {
        background-color: #dc3545;
        color: white;
    }

    .badge-success {
        background-color: #28a745;
        color: white;
    }

    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .btn.add {
        background-color: #28a745;
        color: white;
        font-size: 16px;
        padding: 12px 24px;
        margin-top: 10px;
    }

    .btn.add:hover {
        background-color: #218838;
    }

    .btn-src1 {
        background-color: #007bff;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-src1:hover {
        background-color: #0056b3;
    }

    .form-control {
        width: 100%;
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 2px;
        margin-top: 2px;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 30px;
        border-radius: 8px;
        width: 500px;
        max-width: 90%;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .modal-content h3 {
        margin-top: 0;
        margin-bottom: 20px;
        color: #333;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: #000;
    }

    .pagination-wrapper {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }
</style>
@endpush

<script>
/* ================== GLOBAL ================== */
const modal = document.getElementById('purchaseModal');

let purchaseCart = [];
let totalBiaya = 0;

/* ================== LOAD ================== */
document.addEventListener('DOMContentLoaded', function () {
    const saved = localStorage.getItem('purchaseCart');
    if (saved) {
        purchaseCart = JSON.parse(saved);
    }
    updateSummary();
});

/* ================== STORAGE ================== */
function saveCart() {
    localStorage.setItem('purchaseCart', JSON.stringify(purchaseCart));
}

/* ================== SUMMARY ================== */
function updateSummary() {
    totalBiaya = 0;
    const tbody = document.getElementById('summary-body');
    tbody.innerHTML = '';

    purchaseCart.forEach(item => {
        const total = item.harga_beli * item.jumlah_beli;
        const stokBaru = item.stok_lama + item.jumlah_beli;
        totalBiaya += total;

        tbody.innerHTML += `
            <tr>
                <td>${item.id}</td>
                <td>${item.nama}</td>
                <td>${item.stok_lama}</td>
                <td>${item.jumlah_beli}</td>
                <td>${stokBaru}</td>
                <td>Rp ${item.harga_beli.toLocaleString('id-ID')}</td>
                <td>Rp ${total.toLocaleString('id-ID')}</td>
                <td>
                    <button class="btn btn-danger remove-item" data-id="${item.id}">Hapus</button>
                </td>
            </tr>
        `;
    });

    document.getElementById('total_biaya').innerText =
        'Rp ' + totalBiaya.toLocaleString('id-ID');
}

/* ================== OPEN MODAL ================== */
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('btn-purchase')) {
        const btn = e.target;

        document.getElementById('modal-product-id').value = btn.dataset.id;
        document.getElementById('modal-product-name').value = btn.dataset.name;
        document.getElementById('modal-current-stock').value = btn.dataset.stock;
        document.getElementById('modal-product-price').value = btn.dataset.price;
        document.getElementById('modal-purchase-qty').value = 1;

        calculateModalTotal();
        modal.style.display = 'block';
    }
});

/* ================== CLOSE MODAL ================== */
document.querySelector('.close').onclick = () => modal.style.display = 'none';

window.onclick = e => {
    if (e.target === modal) modal.style.display = 'none';
};

/* ================== MODAL TOTAL ================== */
document.getElementById('modal-purchase-qty').oninput = calculateModalTotal;
document.getElementById('modal-product-price').oninput = calculateModalTotal;

function calculateModalTotal() {
    const qty = parseInt(modal.querySelector('#modal-purchase-qty').value) || 0;
    const price = parseInt(modal.querySelector('#modal-product-price').value) || 0;
    modal.querySelector('#modal-total-cost').value =
        'Rp ' + (qty * price).toLocaleString('id-ID');
}

/* ================== ADD ITEM ================== */
document.getElementById('add-to-summary-btn').onclick = () => {
    const item = {
        id: modal.querySelector('#modal-product-id').value,
        nama: modal.querySelector('#modal-product-name').value,
        stok_lama: parseInt(modal.querySelector('#modal-current-stock').value),
        jumlah_beli: parseInt(modal.querySelector('#modal-purchase-qty').value),
        harga_beli: parseInt(modal.querySelector('#modal-product-price').value),
    };

    if (!item.harga_beli || item.harga_beli <= 0) {
        alert('Harga beli tidak valid');
        return;
    }

    const idx = purchaseCart.findIndex(p => p.id === item.id);
    if (idx !== -1) {
        purchaseCart[idx].jumlah_beli += item.jumlah_beli;
    } else {
        purchaseCart.push(item);
    }

    saveCart();
    updateSummary();
    modal.style.display = 'none';
};

/* ================== REMOVE ITEM ================== */
document.getElementById('summary-body').onclick = e => {
    if (e.target.classList.contains('remove-item')) {
        purchaseCart = purchaseCart.filter(i => i.id !== e.target.dataset.id);
        saveCart();
        updateSummary();
    }
};
document.getElementById('purchase-form').onsubmit = e => {
    e.preventDefault();

    fetch("{{ route('purchase.store') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name=csrf-token]').content
        },
        body: JSON.stringify({
            tanggal_pembelian: document.getElementById('tanggal_pembelian').value,
            supplier: document.getElementById('supplier').value,
            keterangan: document.getElementById('keterangan').value,
            total_biaya: totalBiaya,
            items: purchaseCart
        })
    })
    .then(res => {
        if (!res.ok) throw new Error('Gagal menyimpan');
        return res.json();
    })
    .then(res => {
        alert(res.message);

        if (res.success) {
            localStorage.removeItem('purchaseCart');
            location.reload();
        }
    })
    .catch(err => {
        alert('ERROR: ' + err.message);
        console.error(err);
    });
};

</script>

</body>
</html>
