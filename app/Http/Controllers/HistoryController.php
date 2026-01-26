<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Penjualan | Sistem Inventory dan Kasir</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
@include('layouts.sidebar')
@include('layouts.navbar')
<div class="container">
    <div class="main-content">
        <div class="header-section">
            <h2>Detail Transaksi Penjualan</h2>
            <a href="{{ route('sale.history') }}" class="btn btn-secondary">← Kembali</a>
        </div>

        <!-- Info Transaksi -->
        <div class="info-card">
            <h3>Informasi Transaksi</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>ID Penjualan:</label>
                    <span>{{ $sale->id_penjualan }}</span>
                </div>
                <div class="info-item">
                    <label>Tanggal Transaksi:</label>
                    <span>{{ \Carbon\Carbon::parse($sale->tanggal_transaksi)->format('d F Y, H:i') }}</span>
                </div>
                <div class="info-item">
                    <label>Kasir:</label>
                    <span>{{ $sale->id_user }}</span>
                </div>
                <div class="info-item">
                    <label>Customer:</label>
                    <span>{{ $sale->customer->nama_pelanggan ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Detail Items -->
        <div class="table-wrapper">
            <h3>Detail Produk/Service</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk/Service</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Diskon</th>
                        <th>Harga Setelah Diskon</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach($sale->saleDetails as $detail)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>
                            @if($detail->id_produk)
                                {{ $detail->produk->nama_produk ?? 'Produk Tidak Ditemukan' }}
                            @else
                                {{ $detail->service->service ?? 'Service Tidak Ditemukan' }}
                            @endif
                        </td>
                        <td>
                            @if($detail->id_produk)
                                <span class="badge badge-primary">Produk</span>
                            @else
                                <span class="badge badge-success">Service</span>
                            @endif
                        </td>
                        <td>{{ $detail->jumlah }}</td>
                        <td>{{ $detail->diskon }}%</td>
                        <td>Rp {{ number_format($detail->harga_setelah_diskon, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($detail->harga_setelah_diskon * $detail->jumlah, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="summary-card">
            <h3>Ringkasan Pembayaran</h3>
            <div class="summary-item">
                <label>Total Belanja:</label>
                <span class="amount">Rp {{ number_format($sale->total_belanja, 0, ',', '.') }}</span>
            </div>
            <div class="summary-item">
                <label>Jumlah Bayar:</label>
                <span class="amount">Rp {{ number_format($sale->jumlah_bayar, 0, ',', '.') }}</span>
            </div>
            <div class="summary-item total">
                <label>Kembalian:</label>
                <span class="amount">Rp {{ number_format($sale->kembalian, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Tombol Print (Opsional) -->
        <div class="action-buttons">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print Struk</button>
        </div>
    </div>
</div>

@push('styles')
<style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f4f4;
        color: #333;
    }

    .container {
        margin-top: 30px;
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .header-section h2 {
        color: #007bff;
        margin: 0;
    }

    .info-card {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
        border: 1px solid #ddd;
    }

    .info-card h3 {
        color: #333;
        margin-bottom: 15px;
        font-size: 18px;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-item label {
        font-weight: bold;
        color: #666;
        margin-bottom: 5px;
        font-size: 14px;
    }

    .info-item span {
        color: #333;
        font-size: 16px;
    }

    .table-wrapper {
        margin-top: 20px;
        margin-bottom: 30px;
        overflow-x: auto;
    }

    .table-wrapper h3 {
        color: #333;
        margin-bottom: 15px;
        font-size: 18px;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        background-color: #fff;
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
        font-weight: bold;
    }

    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }

    .badge-primary {
        background-color: #007bff;
        color: white;
    }

    .badge-success {
        background-color: #28a745;
        color: white;
    }

    .summary-card {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #ddd;
        max-width: 400px;
        margin-left: auto;
    }

    .summary-card h3 {
        color: #333;
        margin-bottom: 15px;
        font-size: 18px;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #ddd;
    }

    .summary-item.total {
        border-bottom: none;
        font-weight: bold;
        font-size: 18px;
        color: #28a745;
        margin-top: 10px;
    }

    .summary-item label {
        color: #666;
    }

    .summary-item .amount {
        color: #333;
        font-weight: bold;
    }

    .action-buttons {
        margin-top: 30px;
        text-align: right;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        cursor: pointer;
        border: none;
        margin-left: 10px;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    /* Print Styles */
    @media print {
        .header-section, .action-buttons, .sidebar, .navbar {
            display: none;
        }
        
        .container {
            box-shadow: none;
            padding: 0;
        }
    }
</style>
@endpush

</body>
</html>