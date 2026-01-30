<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sistem Inventory dan Kasir</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Menambahkan Chart.js untuk grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

@include('layouts.sidebar')
@include('layouts.navbar')

<body>
<!-- resources/views/report.blade.php -->
<div class="container">
    <div class="main-content">
        <h2>Laporan Penjualan</h2>

        <!-- Form untuk memilih periode laporan -->
        <form method="GET" action="{{ route('sale.report') }}">
            <div class="form-group">
                <label for="report_type">Pilih Periode Laporan:</label>
                <select name="report_type" class="form-control" id="report_type">
                    <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="weekly" {{ $reportType == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                    <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly" {{ $reportType == 'yearly' ? 'selected' : '' }}>Tahunan</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Tampilkan Laporan</button>
        </form>

        <!-- Menampilkan Total Penjualan dan Keuntungan -->
        <div class="row mt-4">
            <div class="col-md-6">
                <h4>Total Penjualan: Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h4>
            </div>
            <div class="col-md-6">
                <h4>Total Keuntungan: Rp {{ number_format($totalKeuntungan, 0, ',', '.') }}</h4>
            </div>
        </div>

        <!-- Tabel Laporan Penjualan -->
        <table class="table table-striped mt-4">
            <thead>
                <tr>
                    <th>ID Penjualan</th>
                    <th>Tanggal Transaksi</th>
                    <th>Kasir</th>
                    <th>Customer</th>
                    <th>Total Belanja</th>
                    <th>Jumlah Bayar</th>
                    <th>Kembalian</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->id_penjualan }}</td>
                    <td>{{ \Carbon\Carbon::parse($sale->tanggal_transaksi)->format('d F Y') }}</td>
                    <td>{{ $sale->user->nama_user ?? 'Kasir Tidak Ditemukan' }}</td>
                    <td>{{ $sale->customer->nama_pelanggan ?? '-' }}</td>
                    <td>Rp {{ number_format($sale->total_belanja, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($sale->jumlah_bayar, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($sale->kembalian, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>
