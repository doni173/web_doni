<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pembelian | Sistem Inventory dan Kasir</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
@include('layouts.sidebar')
@include('layouts.navbar')

<div class="container">
    <div class="container-daftar">
        <div class="header-section">
            <h2>Detail Pembelian</h2>
            <a href="{{ route('purchase.history') }}" class="btn btn-secondary">Kembali</a>
        </div>

        <!-- Info Pembelian -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">ID Pembelian:</span>
                    <span class="info-value">{{ $pembelian->id_pembelian }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal:</span>
                    <span class="info-value">{{ $pembelian->tanggal_indonesia }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">No. Invoice:</span>
                    <span class="info-value">{{ $pembelian->nomor_invoice }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Supplier:</span>
                    <span class="info-value">{{ $pembelian->nama_supplier }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="badge badge-{{ $pembelian->status == 'completed' ? 'success' : 'warning' }}">
                        {{ ucfirst($pembelian->status) }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Dibuat oleh:</span>
                    <span class="info-value">{{ $pembelian->creator->name ?? 'Admin' }}</span>
                </div>
            </div>
        </div>

        <!-- Detail Item -->
        <div class="table-container">
            <h3>Item yang Dibeli</h3>
            <table class="table-main">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Brand</th>
                        <th>Jumlah</th>
                        <th>Harga Beli</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pembelian->details as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->produk->nama_produk }}</td>
                        <td>{{ $detail->produk->kategori->kategori ?? '-' }}</td>
                        <td>{{ $detail->produk->brand->brand ?? '-' }}</td>
                        <td>{{ $detail->jumlah }} pcs</td>
                        <td>{{ $detail->harga_beli_rupiah }}</td>
                        <td>{{ $detail->subtotal_rupiah }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" style="text-align: right; font-weight: bold;">Total Pembelian:</td>
                        <td style="font-weight: bold; color: #007bff;">{{ $pembelian->total_rupiah }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Summary -->
        <div class="summary-section">
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-icon">📦</div>
                    <div class="summary-content">
                        <span class="summary-title">Total Item</span>
                        <span class="summary-number">{{ $pembelian->details->sum('jumlah') }} pcs</span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">📋</div>
                    <div class="summary-content">
                        <span class="summary-title">Jenis Produk</span>
                        <span class="summary-number">{{ $pembelian->details->count() }} jenis</span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">💰</div>
                    <div class="summary-content">
                        <span class="summary-title">Total Pembelian</span>
                        <span class="summary-number">{{ $pembelian->total_rupiah }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes (jika ada) -->
        @if($pembelian->notes)
        <div class="notes-section">
            <h4>Catatan:</h4>
            <p>{{ $pembelian->notes }}</p>
        </div>
        @endif

        <!-- Actions -->
        <div class="actions-section">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Cetak
            </button>
            <button onclick="exportToPDF()" class="btn btn-success">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
        </div>
    </div>
</div>

<style>
    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .info-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .info-label {
        font-size: 12px;
        color: #666;
        font-weight: 600;
    }

    .info-value {
        font-size: 14px;
        color: #333;
        font-weight: 500;
    }

    .summary-section {
        margin-top: 20px;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .summary-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px;
        color: white;
    }

    .summary-icon {
        font-size: 32px;
    }

    .summary-content {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .summary-title {
        font-size: 12px;
        opacity: 0.9;
    }

    .summary-number {
        font-size: 20px;
        font-weight: bold;
    }

    .notes-section {
        margin-top: 20px;
        padding: 15px;
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        border-radius: 4px;
    }

    .notes-section h4 {
        margin-top: 0;
        margin-bottom: 10px;
        color: #856404;
    }

    .notes-section p {
        margin: 0;
        color: #856404;
    }

    .actions-section {
        margin-top: 20px;
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .badge {
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-success {
        background: #28a745;
        color: white;
    }

    .badge-warning {
        background: #ffc107;
        color: #333;
    }

    tfoot tr {
        background: #f8f9fa;
    }

    @media print {
        .btn, .header-section .btn-secondary, .actions-section {
            display: none;
        }
        
        body {
            background: white;
        }
        
        .container-daftar {
            box-shadow: none;
        }
    }
</style>

<script>
function exportToPDF() {
    // Implementasi export to PDF bisa menggunakan library seperti jsPDF
    // atau mengirim request ke server untuk generate PDF
    alert('Fitur export PDF akan segera tersedia');
}
</script>

</body>
</html>