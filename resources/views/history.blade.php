<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Penjualan | Sistem Inventory dan Kasir</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
@include('layouts.sidebar')
@include('layouts.navbar')
<div class="container">
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>History Penjualan</h2>
            <form method="GET" action="{{ route('sale.history') }}" class="d-flex">
        <input type="date" name="tanggal" class="form-control form-control-sm mr-2" value="{{ request('tanggal') }}" placeholder="Pilih Tanggal">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>
        </div>
        
        <!-- Tabel Daftar Transaksi -->
        <div class="table-wrapper">
            <div class="table-container">
                <h3>Daftar Transaksi</h3>
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
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td>{{ $sale->id_penjualan }}</td>
                            <td>{{ \Carbon\Carbon::parse($sale->tanggal_transaksi)->format('d/m/Y') }}</td>
                            <td>{{ $sale->id_user }}</td>
                            <td>{{ $sale->customer->nama_pelanggan ?? '-' }}</td>
                            <td>Rp {{ number_format($sale->total_belanja, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($sale->jumlah_bayar, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($sale->kembalian, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('sale.show', $sale->id_penjualan) }}" class="btn-detail">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada transaksi penjualan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination (jika ada) -->
        @if(isset($sales) && $sales instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="pagination-wrapper">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    /* General Styles */
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f4f4;
        color: #333;
    }

    .container {
        margin-top: 30px;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .main-content h2 {
        color: #007bff;
        margin-bottom: 20px;
    }

    .table-wrapper {
        margin-top: 20px;
        overflow-x: auto;
    }

    .table-container {
        margin-bottom: 20px;
    }

    .table-container h3 {
        color: #333;
        margin-bottom: 15px;
        font-size: 18px;
    }

    .table {
        width: 100%;
        margin-top: 20px;
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

    .table tbody tr:hover {
        background-color: #e9f5ff;
    }

    .text-center {
        text-align: center;
        padding: 20px;
        color: #999;
    }

    .btn {
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        cursor: pointer;
        border: none;
    }

    .btn-info {
        background-color: #17a2b8;
        color: white;
    }

    .btn-info:hover {
        background-color: #138496;
    }

    .btn-sm {
        padding: 4px 8px;
        font-size: 12px;
    }

    .pagination-wrapper {
        margin-top: 20px;
        text-align: center;
    }
</style>
@endpush

</body>
</html>