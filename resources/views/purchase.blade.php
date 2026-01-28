<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pembelian | Sistem Inventory dan Kasir</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
@include('layouts.sidebar')
@include('layouts.navbar')
<div class="container">
    <div class="container-daftar">
        <div class="">
            <div class="padding">
            <h2>Transaksi Pembelian</h2>
            </div>
        </div>  
        <!-- Wrapper untuk tabel Daftar Barang -->
        <div class="table-wrapper">
            <!-- Daftar Barang untuk Pembelian -->
            <div class="table-container">
                <form action="{{ route('purchase.index') }}" method="GET" class="search-form">
                    <input type="text" class="form-control" placeholder="Cari barang..." name="q_barang" value="{{ request('q_barang') }}" style="width: 300px;">
                    <button type="submit" class="btn-src1">Search</button>
                </form>
                <table class="table-main">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga Beli</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>{{ $item->nama_produk }}</td>
                            <td>{{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                            <td>
                                <input type="number" name="qty" class="form-control qty" data-id="{{ $item->id_produk }}" value="1" min="1" max="1000">
                            </td>
                            <td>
                                <button type="button" class="btn btn-success add-item" 
                                    data-id="{{ $item->id_produk }}" 
                                    data-name="{{ $item->nama_produk }}" 
                                    data-price="{{ $item->harga_beli }}"
                                    data-type="produk">Tambah</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="container-ringkasan"> 
    <table class="table-main" id="summary-table">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Harga Beli</th>
                <th>Jumlah</th>
                <th>Total Harga</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="summary-body">
            <!-- Data yang ditambahkan akan muncul di sini -->
        </tbody>    
    </table>
</div>

<div class="container-bayar">
    <form id="purchase-form">
        @csrf
        <div class="form-wrapper">     
            <div class="form-group">
                <label for="tanggal_pembelian">Tanggal Pembelian</label>
                <input type="date" id="tanggal_pembelian" name="tanggal_pembelian" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label for="nama_supplier">Nama Supplier</label>
                <input type="text" id="nama_supplier" name="nama_supplier" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="no_invoice">No. Invoice</label>
                <input type="text" id="no_invoice" name="no_invoice" class="form-control" placeholder="INV-001" required>
            </div>

            <div class="form-group">
                <label for="metode_pembayaran">Metode Pembayaran</label>
                <select id="metode_pembayaran" name="metode_pembayaran" class="form-control" required>
                    <option value="">Pilih Metode</option>
                    <option value="tunai">Tunai</option>
                    <option value="transfer">Transfer</option>
                    <option value="kredit">Kredit/Tempo</option>
                </select>
            </div>
        
            <div class="form-group">
                <h4>Total Pembelian: <span id="total_pembelian">Rp. 0</span></h4>
            </div>
        </div>
        <button type="submit" class="btn add">Selesaikan Pembelian</button>
    </form>
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
        margin-top : 1px;
    }
    .container-daftar h3 {
        margin-top : 1px;
    }
    
    .container-ringkasan {
        margin-top: 15px;
        background-color: #fff;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .container-ringkasan h2 {
        margin-top : 1px;
    }

    .container-ringkasan h3 {
        margin-top : 1px;
    }

    .container-bayar {
        font-family: 'Open Sans', sans-serif;
        margin-top: 15px;
        background-color: #fff;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .container-bayar h2 {
        margin-top : 1px;
    }

    .container-bayar h3 {
        margin-top : 1px;
    }

    .form-wrapper {
        display: flex;
        gap: 5px;
    }

    .form-wrapper1 {
        display: flex;
        gap: 120px;
    }

    .form-group {
        margin-top: 8px;
        margin-left: 10px;
    }

    .table-wrapper {
        display: flex;
        gap: 10px;
    }

    .table-container {
        margin-bottom: 5px;
        flex: 1;
    }
    .table-container h3 {
        margin-bottom: 1px;
    }

    .table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
    }

    .table-striped tr:nth-child(odd) {
        background-color: #f9f9f9;
    }

    .table th, .table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .table th {
        background-color: #007bff;
        color: #fff;
    }

    .btn-success {
        background-color: #28a745;
        color: white;
    }

    .btn-success:hover {
        background-color: #218838;
    }

    .form-control {
        width: 100%;
        padding: 8px;
        font-size: 10px;
        margin-bottom: 2px;
        margin-top: 2px;
    }

    .form-control-qty{ 
        width: 100%;
        padding: 2px;
        font-size: 10px;
    }
    .form-control2 {
        width: 100%;
        padding: 8px;
        font-size: 10px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    loadCartFromLocalStorage();
    updateSummary();
});

let cart = [];
let totalPembelian = 0;

function saveCartToLocalStorage() {
    localStorage.setItem('purchaseCart', JSON.stringify(cart));
}

function loadCartFromLocalStorage() {
    const savedCart = localStorage.getItem('purchaseCart');
    if (savedCart) {
        try {
            cart = JSON.parse(savedCart);
        } catch (e) {
            cart = [];
        }
    }
}

function updateSummary() {
    totalPembelian = 0;
    const tbody = document.getElementById('summary-body');
    tbody.innerHTML = '';

    cart.forEach(item => {
        const totalItem = item.harga_beli * item.jumlah;
        totalPembelian += totalItem;

        tbody.innerHTML += `
            <tr>
                <td>${item.nama}</td>
                <td>Rp ${item.harga_beli.toLocaleString('id-ID')}</td>
                <td>${item.jumlah}</td>
                <td>Rp ${totalItem.toLocaleString('id-ID')}</td>
                <td><button type="button" class="btn btn-danger remove-item" data-id="${item.id}" data-type="${item.type}">Hapus</button></td>
            </tr>
        `;
    });

    document.getElementById('total_pembelian').innerText = 'Rp. ' + totalPembelian.toLocaleString('id-ID');
}

function addToCart(id, nama, harga, jumlah, type) {
    // Validasi input
    if (!id || !nama || isNaN(harga) || isNaN(jumlah)) {
        console.error('Data tidak valid:', { id, nama, harga, jumlah, type });
        alert('Data produk tidak valid!');
        return;
    }

    const uniqueKey = type + '_' + id;
    const existing = cart.find(item => (item.type + '_' + item.id) === uniqueKey);

    if (existing) {
        existing.jumlah += jumlah;
    } else {
        const newItem = { 
            id: String(id),
            nama: String(nama),
            harga_beli: Number(harga),
            jumlah: Number(jumlah),
            type: String(type)
        };
        
        console.log('Menambahkan item ke cart:', newItem);
        cart.push(newItem);
    }

    saveCartToLocalStorage();
    updateSummary();
}

function removeFromCart(id, type) {
    const uniqueKey = type + '_' + id;
    const index = cart.findIndex(item => (item.type + '_' + item.id) === uniqueKey);
    
    if (index !== -1) {
        cart.splice(index, 1);
    }
    
    saveCartToLocalStorage();
    updateSummary();
}

// ITEM
document.querySelectorAll('.add-item').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const tr = this.closest('tr');
        const jumlah = parseInt(tr.querySelector('.qty').value) || 1;
        const harga = parseFloat(this.dataset.price);

        console.log('Item - Data:', {
            id: this.dataset.id,
            name: this.dataset.name,
            price: harga,
            qty: jumlah
        });

        if (isNaN(harga) || harga <= 0) {
            alert('Harga produk tidak valid!');
            return;
        }

        addToCart(
            this.dataset.id,
            this.dataset.name,
            harga,
            jumlah,
            'produk'
        );
    });
});

// REMOVE ITEM
document.getElementById('summary-body').addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('remove-item')) {
        e.preventDefault();
        const itemId = e.target.dataset.id;
        const itemType = e.target.dataset.type;
        removeFromCart(itemId, itemType);
    }
});

// HANDLE SUBMIT FORM - SELESAIKAN PEMBELIAN
document.getElementById('purchase-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (cart.length === 0) {
        alert('Keranjang pembelian kosong! Silakan tambahkan produk terlebih dahulu.');
        return;
    }
    
    const tanggalPembelian = document.getElementById('tanggal_pembelian').value;
    const namaSupplier = document.getElementById('nama_supplier').value;
    const noInvoice = document.getElementById('no_invoice').value;
    const metodePembayaran = document.getElementById('metode_pembayaran').value;
    
    if (!tanggalPembelian || !namaSupplier || !noInvoice || !metodePembayaran) {
        alert('Mohon lengkapi semua data pembelian!');
        return;
    }
    
    // Format items sesuai ekspektasi backend
    const formattedItems = cart.map(item => {
        const formattedItem = {
            id: String(item.id),
            type: String(item.type),
            nama: String(item.nama),
            harga_beli: Number(item.harga_beli),
            jumlah: Number(item.jumlah),
            total: Number(item.harga_beli * item.jumlah)
        };
        
        console.log('Formatted item:', formattedItem);
        return formattedItem;
    });
    
    // Validasi items sebelum dikirim
    const invalidItems = formattedItems.filter(item => 
        isNaN(item.harga_beli) || 
        item.harga_beli <= 0 || 
        isNaN(item.jumlah) || 
        item.jumlah <= 0
    );
    
    if (invalidItems.length > 0) {
        console.error('Items tidak valid:', invalidItems);
        alert('Ada item dengan harga atau jumlah tidak valid! Mohon periksa kembali.');
        return;
    }
    
    const data = {
        tanggal_pembelian: tanggalPembelian,
        nama_supplier: namaSupplier,
        no_invoice: noInvoice,
        metode_pembayaran: metodePembayaran,
        total_pembelian: totalPembelian,
        items: formattedItems
    };
    
    console.log('Data yang akan dikirim:', JSON.stringify(data, null, 2));
    
    try {
        const response = await fetch('{{ route("purchase.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        console.log('Response dari server:', result);
        
        if (result.success) {
            alert('Transaksi pembelian berhasil disimpan!\nID Pembelian: ' + result.data.id_pembelian);
            
            localStorage.removeItem('purchaseCart');
            cart = [];
            
            document.getElementById('purchase-form').reset();
            document.getElementById('tanggal_pembelian').value = '{{ date("Y-m-d") }}';
            
            updateSummary();
            
        } else {
            alert('Gagal menyimpan transaksi: ' + (result.message || 'Terjadi kesalahan'));
            console.error('Error detail:', result);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan transaksi pembelian!');
    }
});
</script>

</body>
</html>  