<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Penjualan | Sistem Inventory dan Kasir</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
@include('layouts.sidebar')
@include('layouts.navbar')
<div class="container">
    <div class="container-daftar">
        <div class="">
            <div class="padding">
            <h2>Transaksi Penjualan</h2>
            </div>
        </div>  
        <!-- Wrapper untuk tabel Daftar Barang dan Daftar Service -->
        <div class="table-wrapper">
            <!-- Daftar Barang -->
            <div class="table-container">
                <form action="{{ route('sale.index') }}" method="GET" class="search-form">
                    <input type="text" class="form-control" placeholder="Cari barang..." name="q_barang" value="{{ request('q_barang') }}" style="width: 300px;">
                    <button type="submit" class="btn-src1">Search</button>
                </form>
                <table class="table-main">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                      @foreach($items as $item)
<tr>
    <td>{{ $item->nama_produk }}</td>
    <td>{{ number_format($item->harga_setelah_diskon, 0, ',', '.') }}</td>
    <td>
        <input type="number" 
               name="qty" 
               class="form-control qty"
               data-id="{{ $item->id_produk }}" 
               value="1" 
               min="1"
               max="{{ $item->stok }}"
               {{ $item->stok == 0 ? 'disabled' : '' }}>
    </td>
    <td>
        <button type="button"
            class="btn btn-success add-item"
            data-id="{{ $item->id_produk }}"
            data-name="{{ $item->nama_produk }}"
            data-price="{{ $item->harga_jual }}"
            data-discount="{{ $item->diskon }}"
            data-stok="{{ $item->stok }}"
            data-type="produk"
            {{ $item->stok == 0 ? 'disabled' : '' }}>
            
            {{ $item->stok == 0 ? 'Stok Habis' : 'Tambah' }}
        </button>
    </td>
</tr>
@endforeach

                    </tbody>
                </table>
            </div>

            <!-- Daftar Service -->
            <div class="table-container">
                <form action="{{ route('sale.index') }}" method="GET" class="search-form">
                    <input type="text" class="form-control" placeholder="Cari service..." name="q_service" value="{{ request('q_service') }}" style="width: 300px;">
                    <button type="submit" class="btn-src1">Search</button>
                </form>
                <table class="table-main">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $service)
                        <tr>
                            <td>{{ $service->service }}</td>
                            <td>{{ number_format($service->harga_setelah_diskon, 0, ',', '.') }}</td>
                            <td>
                                <input type="number" name="qty" class="form-control qty" data-id="{{ $service->id_service }}" value="1" min="1" max="2">
                            </td>
                            <td>
                                <button type="button" class="btn btn-success add-service" 
                                    data-id="{{ $service->id_service }}" 
                                    data-name="{{ $service->service }}" 
                                    data-price="{{ $service->harga_jual }}"
                                    data-discount="0"
                                    data-type="service">Tambah</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
            </table>
            
        </div>
    </div>
</div>

<div class="container-ringkasan"> 
    <table class="table-main" id="summary-table">
        <thead>
            <tr>
                <th>Produk / Service</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Diskon</th>
                <th>Harga Setelah Diskon</th>
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
    <form id="sale-form">
        @csrf
        <div class="form-wrapper">     
            <div class="form-group">
                <label for="tanggal_transaksi">Tanggal Transaksi</label>
                <input type="date" id="tanggal_transaksi" name="tanggal_transaksi" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label for="nama_pelanggan">Nama Pelanggan</label>
                <input type="text" id="nama_pelanggan" name="nama_pelanggan" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="jumlah_bayar">Jumlah Bayar</label>
                <input type="number" id="jumlah_bayar" name="jumlah_bayar" class="form-control" required>
            </div>
        
            <div class="form-group">
                <h4>Total Belanja: <span id="total_harga">Rp. 0</span></h4>
            </div>

            <div class="form-group"> 
                <h4>Kembalian: <span id="kembalian">Rp. 0</span></h4>
            </div>
        </div>
        <button type="submit" class="btn add">Selesaikan Penjualan</button>
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
let totalBelanja = 0;

function saveCartToLocalStorage() {
    localStorage.setItem('salesCart', JSON.stringify(cart));
}

function loadCartFromLocalStorage() {
    const savedCart = localStorage.getItem('salesCart');
    if (savedCart) {
        try {
            cart = JSON.parse(savedCart);
        } catch (e) {
            cart = [];
        }
    }
}

function updateSummary() {
    totalBelanja = 0;
    const tbody = document.getElementById('summary-body');
    tbody.innerHTML = '';

    cart.forEach(item => {
        const totalItem = item.harga_setelah_diskon * item.jumlah;
        totalBelanja += totalItem;

        tbody.innerHTML += `
            <tr>
                <td>${item.nama}</td>
                <td>Rp ${item.harga_jual.toLocaleString('id-ID')}</td>
                <td>${item.jumlah}</td>
                <td>${item.diskon}%</td>
                <td>Rp ${item.harga_setelah_diskon.toLocaleString('id-ID')}</td>
                <td>Rp ${totalItem.toLocaleString('id-ID')}</td>
                <td><button type="button" class="btn btn-danger remove-item" data-id="${item.id}" data-type="${item.type}">Hapus</button></td>
            </tr>
        `;
    });

    document.getElementById('total_harga').innerText = 'Rp. ' + totalBelanja.toLocaleString('id-ID');
    hitungKembalian();
}

function addToCart(id, nama, harga, jumlah, diskon, type) {
    // Validasi input
    if (!id || !nama || isNaN(harga) || isNaN(jumlah) || isNaN(diskon)) {
        console.error('Data tidak valid:', { id, nama, harga, jumlah, diskon, type });
        alert('Data produk/service tidak valid!');
        return;
    }

    const harga_setelah_diskon = harga - (harga * (diskon / 100));
    const uniqueKey = type + '_' + id;
    const existing = cart.find(item => (item.type + '_' + item.id) === uniqueKey);

    if (existing) {
        existing.jumlah += jumlah;
    } else {
        const newItem = { 
            id: String(id),
            nama: String(nama),
            harga_jual: Number(harga),
            jumlah: Number(jumlah),
            diskon: Number(diskon),
            harga_setelah_diskon: Number(harga_setelah_diskon),
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

function hitungKembalian() {
    const bayar = parseInt(document.getElementById('jumlah_bayar').value) || 0;
    let kembali = bayar - totalBelanja;
    if (kembali < 0) kembali = 0;

    document.getElementById('kembalian').innerText = 'Rp. ' + kembali.toLocaleString('id-ID');
}

document.getElementById('jumlah_bayar').addEventListener('input', hitungKembalian);

// ITEM
document.querySelectorAll('.add-item').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const tr = this.closest('tr');
        const jumlah = parseInt(tr.querySelector('.qty').value) || 1;
        const diskon = parseFloat(this.dataset.discount) || 0;
        const harga = parseFloat(this.dataset.price);

        console.log('Item - Data:', {
            id: this.dataset.id,
            name: this.dataset.name,
            price: harga,
            discount: diskon,
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
            diskon,
            'produk'
        );
    });
});

// SERVICE
document.querySelectorAll('.add-service').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const tr = this.closest('tr');
        const jumlah = parseInt(tr.querySelector('.qty').value) || 1;
        const harga = parseFloat(this.dataset.price);
        const diskon = parseFloat(this.dataset.discount) || 0;

        console.log('Service - Data:', {
            id: this.dataset.id,
            name: this.dataset.name,
            price: harga,
            discount: diskon,
            qty: jumlah
        });

        if (isNaN(harga) || harga <= 0) {
            alert('Harga service tidak valid!');
            return;
        }

        addToCart(
            this.dataset.id,
            this.dataset.name,
            harga,
            jumlah,
            diskon,
            'service'
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

// HANDLE SUBMIT FORM - SELESAIKAN PENJUALAN
document.getElementById('sale-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (cart.length === 0) {
        alert('Keranjang belanja kosong! Silakan tambahkan produk/service terlebih dahulu.');
        return;
    }
    
    const tanggalTransaksi = document.getElementById('tanggal_transaksi').value;
    const namaPelanggan = document.getElementById('nama_pelanggan').value;
    const jumlahBayar = parseFloat(document.getElementById('jumlah_bayar').value);
    
    if (!tanggalTransaksi || !namaPelanggan || !jumlahBayar) {
        alert('Mohon lengkapi semua data transaksi!');
        return;
    }
    
    if (jumlahBayar < totalBelanja) {
        alert('Jumlah bayar kurang dari total belanja!');
        return;
    }
    
    // Format items sesuai ekspektasi backend
    const formattedItems = cart.map(item => {
        const formattedItem = {
            id: String(item.id),
            type: String(item.type),
            nama: String(item.nama),
            harga_jual: Number(item.harga_jual),
            jumlah: Number(item.jumlah),
            diskon: Number(item.diskon),
            harga_setelah_diskon: Number(item.harga_setelah_diskon),
            total: Number(item.harga_setelah_diskon * item.jumlah)
        };
        
        console.log('Formatted item:', formattedItem);
        return formattedItem;
    });
    
    // Validasi items sebelum dikirim
    const invalidItems = formattedItems.filter(item => 
        isNaN(item.harga_jual) || 
        item.harga_jual <= 0 || 
        isNaN(item.jumlah) || 
        item.jumlah <= 0
    );
    
    if (invalidItems.length > 0) {
        console.error('Items tidak valid:', invalidItems);
        alert('Ada item dengan harga atau jumlah tidak valid! Mohon periksa kembali.');
        return;
    }
    
    const data = {
        tanggal_transaksi: tanggalTransaksi,
        nama_pelanggan: namaPelanggan,
        jumlah_bayar: jumlahBayar,
        total_belanja: totalBelanja,
        kembalian: jumlahBayar - totalBelanja,
        items: formattedItems
    };
    
    console.log('Data yang akan dikirim:', JSON.stringify(data, null, 2));
    
    try {
        const response = await fetch('{{ route("sale.store") }}', {
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
            alert('Transaksi berhasil disimpan!\nID Transaksi: ' + result.data.id_penjualan);
            
            localStorage.removeItem('salesCart');
            cart = [];
            
            document.getElementById('sale-form').reset();
            document.getElementById('tanggal_transaksi').value = '{{ date("d-m-y") }}';
            
            updateSummary();
            
        } else {
            alert('Gagal menyimpan transaksi: ' + (result.message || 'Terjadi kesalahan'));
            console.error('Error detail:', result);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan transaksi!');
    }
});
</script>

</body>
</html>