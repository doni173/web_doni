<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pembelian | Sistem Inventory dan Kasir</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
@include('layouts.sidebar')
@include('layouts.navbar')

<div class="container">
    <div class="container-daftar">
        <div class="padding">
            <h2>Riwayat Pembelian</h2>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form action="{{ route('purchase.history') }}" method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="start_date">Dari Tanggal:</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                </div>

                <div class="filter-group">
                    <label for="end_date">Sampai Tanggal:</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                </div>

                <div class="filter-group">
                    <label for="supplier">Supplier:</label>
                    <input type="text" id="supplier" name="supplier" placeholder="Nama supplier..." value="{{ request('supplier') }}" class="form-control">
                </div>

                <div class="filter-group">
                    <label for="invoice">No. Invoice:</label>
                    <input type="text" id="invoice" name="invoice" placeholder="Nomor invoice..." value="{{ request('invoice') }}" class="form-control">
                </div>

                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('purchase.history') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <table class="table-main">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>No. Invoice</th>
                        <th>Supplier</th>
                        <th>Total Pembelian</th>
                        <th>Jumlah Item</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->id_pembelian }}</td>
                        <td>{{ $purchase->tanggal_indonesia }}</td>
                        <td>{{ $purchase->nomor_invoice }}</td>
                        <td>{{ $purchase->nama_supplier }}</td>
                        <td>{{ $purchase->total_rupiah }}</td>
                        <td>{{ $purchase->details->sum('jumlah') }} pcs</td>
                        <td>
                            <span class="badge badge-{{ $purchase->status == 'completed' ? 'success' : 'warning' }}">
                                {{ ucfirst($purchase->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('purchase.show', $purchase->id_pembelian) }}" class="btn btn-sm btn-info">Detail</a>
                            <button type="button" class="btn btn-sm btn-danger delete-purchase" data-id="{{ $purchase->id_pembelian }}">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center;">Tidak ada data pembelian</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-container">
                {{ $purchases->links() }}
            </div>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <h3>Ringkasan</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="summary-label">Total Transaksi:</span>
                    <span class="summary-value">{{ $purchases->total() }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Pembelian:</span>
                    <span class="summary-value">Rp {{ number_format($purchases->sum('total_pembelian'), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .filter-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .filter-form {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filter-group label {
        font-size: 12px;
        font-weight: 600;
        color: #333;
    }

    .summary-section {
        margin-top: 20px;
        padding: 15px;
        background: #e3f2fd;
        border-radius: 8px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 10px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        background: white;
        border-radius: 5px;
    }

    .summary-label {
        font-weight: 600;
    }

    .summary-value {
        color: #007bff;
        font-weight: bold;
    }

    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
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

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }

    .btn-info {
        background: #17a2b8;
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .pagination-container {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete purchase
    document.querySelectorAll('.delete-purchase').forEach(btn => {
        btn.addEventListener('click', function() {
            const purchaseId = this.dataset.id;
            
            if (confirm('Apakah Anda yakin ingin menghapus transaksi pembelian ini? Stok akan dikurangi kembali.')) {
                fetch(`/purchase/${purchaseId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pembelian berhasil dihapus');
                        location.reload();
                    } else {
                        alert('Gagal menghapus pembelian: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus pembelian');
                });
            }
        });
    });
});
</script>

</body>
</html>