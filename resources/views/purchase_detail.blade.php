<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pembelian - {{ $purchase->id_pembelian }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        .detail-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .detail-header {
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .detail-header h2 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .info-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-size: 1.1em;
        }

        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }

        .table-main {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .table-main thead {
            background-color: #333;
            color: white;
        }

        .table-main th,
        .table-main td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table-main tbody tr:hover {
            background-color: #f5f5f5;
        }

        .total-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }

        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        .btn-print {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-left: 10px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-print:hover {
            background-color: #218838;
        }

        @media print {
            .btn-back, .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    @include('layouts.sidebar')
    @include('layouts.navbar')

    <div class="container">
    <div class="main-container">
        <div class="detail-header">
            <h2>Detail Pembelian</h2>
            <p>ID Pembelian: <strong>{{ $purchase->id_pembelian }}</strong></p>
        </div>

        <!-- Informasi Pembelian -->
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Tanggal Pembelian</div>
                <div class="info-value">{{ date('d-m-Y', strtotime($purchase->tgl_pembelian)) }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">Supplier</div>
                <div class="info-value">{{ $purchase->supplier->nama_supplier ?? '-' }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">Total Pembelian</div>
                <div class="info-value">Rp {{ number_format($purchase->total_pembelian, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Tabel Detail Produk -->
        <h3>Detail Produk</h3>
        <div class="table-container">
            <table class="table-main">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Produk</th>
                        <th>Nama Produk</th>
                        <th>Supplier</th>
                        <th>Stok Lama</th>
                        <th>Jumlah Beli</th>
                        <th>Stok Baru</th>
                        <th>Harga Beli</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($details as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->id_produk }}</td>
                        <td>{{ $detail->item->nama_produk ?? '-' }}</td>
                        <td>{{ $detail->supplier->nama_supplier ?? '-' }}</td>
                        <td>{{ $detail->stok_lama }}</td>
                        <td>{{ $detail->jumlah_beli }}</td>
                        <td>{{ $detail->stok_baru }}</td>
                        <td>Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total -->
        <div class="total-section">
            <div class="total-row">
                <span>Total Pembelian:</span>
                <span>Rp {{ number_format($purchase->total_pembelian, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div style="margin-top: 20px;">
            <a href="{{ route('purchase.history') }}" class="btn-back">
                ← Kembali ke History
            </a>
            <button onclick="window.print()" class="btn-print">
                🖨️ Print
            </button>
        </div>
    </div>  
    </div>
</body>
</html>