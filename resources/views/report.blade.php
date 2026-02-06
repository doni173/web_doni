<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan | Sistem Inventory dan Kasir</title>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

@include('layouts.navbar')
@include('layouts.sidebar')
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="main-container">
<div class="main-content">

<h2>Laporan Penjualan</h2>

<!-- ================= FILTER PERIODE ================= -->
<div class="filter-section">
    <form method="GET" action="{{ route('sale.report') }}" class="filter-form" id="filterForm">
        <div class="filter-row">
            <!-- Pilih Tipe Periode -->
            <div class="form-group-filter">
                <label for="report_type">
                    <i class="bi bi-calendar-range"></i>
                    Tipe Laporan
                </label>
                <select name="report_type" class="form-control-select" id="report_type">
                    <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="custom" {{ $reportType == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly" {{ $reportType == 'yearly' ? 'selected' : '' }}>Tahunan</option>
                </select>
            </div>

            <!-- Filter Harian - Pilih Tanggal -->
            <div class="form-group-filter" id="daily-filter" style="display: none;">
                <label for="date">
                    <i class="bi bi-calendar-date"></i>
                    Pilih Tanggal
                </label>
                <input type="date" 
                       name="date" 
                       id="date" 
                       class="form-control-input"
                       value="{{ request('date', date('Y-m-d')) }}">
            </div>

            <!-- Filter Custom Range - Dari & Sampai -->
            <div class="form-group-filter-range" id="custom-filter" style="display: none;">
                <div class="date-range-group">
                    <div class="date-input-wrapper">
                        <label for="start_date">
                            <i class="bi bi-calendar-check"></i>
                            Dari Tanggal
                        </label>
                        <input type="date" 
                               name="start_date" 
                               id="start_date" 
                               class="form-control-input"
                               value="{{ request('start_date', date('Y-m-01')) }}">
                    </div>
                    <div class="date-separator">
                        <i class="bi bi-arrow-right"></i>
                    </div>
                    <div class="date-input-wrapper">
                        <label for="end_date">
                            <i class="bi bi-calendar-x"></i>
                            Sampai Tanggal
                        </label>
                        <input type="date" 
                               name="end_date" 
                               id="end_date" 
                               class="form-control-input"
                               value="{{ request('end_date', date('Y-m-d')) }}">
                    </div>
                </div>
            </div>

            <!-- Filter Bulanan - Pilih Bulan -->
            <div class="form-group-filter" id="monthly-filter" style="display: none;">
                <label for="month">
                    <i class="bi bi-calendar2-month"></i>
                    Pilih Bulan
                </label>
                <input type="month" 
                       name="month" 
                       id="month" 
                       class="form-control-input"
                       value="{{ request('month', date('Y-m')) }}">
            </div>

            <!-- Filter Tahunan - Pilih Tahun -->
            <div class="form-group-filter" id="yearly-filter" style="display: none;">
                <label for="year">
                    <i class="bi bi-calendar3"></i>
                    Pilih Tahun
                </label>
                <select name="year" id="year" class="form-control-select">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Buttons -->
            <div class="filter-actions">
                <button type="submit" class="btn-filter">
                    <i class="bi bi-funnel-fill"></i>
                    Tampilkan Laporan
                </button>
                <button type="button" class="btn-export" onclick="exportToPDF()">
                    <i class="bi bi-file-pdf-fill"></i>
                    Export PDF
                </button>
                <button type="button" class="btn-reset" onclick="resetFilter()">
                    <i class="bi bi-arrow-clockwise"></i>
                    Reset
                </button>
            </div>
        </div>
    </form>
</div>

<!-- ================= PERIODE INFO ================= -->
<div class="period-info">
    <i class="bi bi-info-circle"></i>
    <span>
        Menampilkan laporan 
        <strong>
            @if($reportType == 'daily')
                {{ request('date') ? \Carbon\Carbon::parse(request('date'))->translatedFormat('d F Y') : 'Hari Ini' }}
            @elseif($reportType == 'custom')
                {{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->translatedFormat('d M Y') : '' }} 
                - 
                {{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->translatedFormat('d M Y') : '' }}
            @elseif($reportType == 'monthly')
                {{ request('month') ? \Carbon\Carbon::parse(request('month'))->translatedFormat('F Y') : 'Bulan Ini' }}
            @elseif($reportType == 'yearly')
                Tahun {{ request('year', date('Y')) }}
            @endif
        </strong>
    </span>
</div>

<!-- ================= STATISTIK CARDS ================= -->
<div class="stats-container">
    <div class="stat-card stat-primary">
        <div class="stat-icon">
            <i class="bi bi-cash-stack"></i>
        </div>
        <div class="stat-content">
            <h3>Total Penjualan</h3>
            <p class="stat-value">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</p>
            <span class="stat-label">Total Revenue</span>
        </div>
    </div>

    <div class="stat-card stat-success">
        <div class="stat-icon">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="stat-content">
            <h3>Total Keuntungan</h3>
            <p class="stat-value">Rp {{ number_format($totalKeuntungan, 0, ',', '.') }}</p>
            <span class="stat-label">Profit Margin</span>
        </div>
    </div>

    <div class="stat-card stat-info">
        <div class="stat-icon">
            <i class="bi bi-receipt"></i>
        </div>
        <div class="stat-content">
            <h3>Total Transaksi</h3>
            <p class="stat-value">{{ $sales->count() }}</p>
            <span class="stat-label">Transaksi Berhasil</span>
        </div>
    </div>

    <div class="stat-card stat-warning">
        <div class="stat-icon">
            <i class="bi bi-calculator"></i>
        </div>
        <div class="stat-content">
            <h3>Rata-rata</h3>
            <p class="stat-value">Rp {{ number_format($sales->count() > 0 ? $totalPenjualan / $sales->count() : 0, 0, ',', '.') }}</p>
            <span class="stat-label">Per Transaksi</span>
        </div>
    </div>
</div>

<!-- ================= TABEL LAPORAN ================= -->
<div class="table-responsive">
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
    <th>Status</th>
</tr>
</thead>
<tbody>
@forelse($sales as $sale)
<tr>
    <td data-label="ID Penjualan">
        <span class="id-badge">{{ $sale->id_penjualan }}</span>
    </td>
    <td data-label="Tanggal">
        <div class="date-cell">
            <i class="bi bi-calendar-event"></i>
            <div>
                {{-- PERBAIKAN: Gunakan Carbon dengan timezone untuk parsing dan format --}}
                @php
                    $tanggal = \Carbon\Carbon::parse($sale->tanggal_transaksi)->timezone('Asia/Jakarta');
                @endphp
                <div class="date-main">{{ $tanggal->translatedFormat('d F Y') }}</div>
                <div class="date-time">{{ $tanggal->format('H:i') }} WIB</div>
            </div>
        </div>
    </td>
    <td data-label="Kasir">
        <div class="user-cell">
            <i class="bi bi-person-badge-fill"></i>
            {{ $sale->user->nama_user ?? 'Kasir Tidak Ditemukan' }}
        </div>
    </td>
    <td data-label="Customer">
        <div class="customer-cell">
            <i class="bi bi-person-fill"></i>
            {{ $sale->customer->nama_pelanggan ?? 'Umum' }}
        </div>
    </td>
    <td data-label="Total Belanja">
        <span class="price-text">Rp {{ number_format($sale->total_belanja, 0, ',', '.') }}</span>
    </td>
    <td data-label="Jumlah Bayar">
        <span class="pay-text">Rp {{ number_format($sale->jumlah_bayar, 0, ',', '.') }}</span>
    </td>
    <td data-label="Kembalian">
        <span class="change-text">Rp {{ number_format($sale->kembalian, 0, ',', '.') }}</span>
    </td>
    <td data-label="Status">
        <span class="badge-success">
            <i class="bi bi-check-circle-fill"></i>
            Selesai
        </span>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center">
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Tidak ada data penjualan untuk periode ini</p>
        </div>
    </td>
</tr>
@endforelse
</tbody>
<tfoot>
<tr class="table-footer">
    <td colspan="4" class="text-right">
        <strong><i class="bi bi-calculator"></i> GRAND TOTAL</strong>
    </td>
    <td>
        <strong class="total-highlight">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</strong>
    </td>
    <td colspan="3"></td>
</tr>
</tfoot>
</table>
</div>

</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('active');
}

// ================= FILTER TOGGLE ================= 
document.getElementById('report_type').addEventListener('change', function() {
    const value = this.value;
    
    // Hide all filters
    document.getElementById('daily-filter').style.display = 'none';
    document.getElementById('custom-filter').style.display = 'none';
    document.getElementById('monthly-filter').style.display = 'none';
    document.getElementById('yearly-filter').style.display = 'none';
    
    // Show relevant filter
    if (value === 'daily') {
        document.getElementById('daily-filter').style.display = 'block';
    } else if (value === 'custom') {
        document.getElementById('custom-filter').style.display = 'block';
    } else if (value === 'monthly') {
        document.getElementById('monthly-filter').style.display = 'block';
    } else if (value === 'yearly') {
        document.getElementById('yearly-filter').style.display = 'block';
    }
});

// Trigger on page load
document.addEventListener('DOMContentLoaded', function() {
    const reportType = document.getElementById('report_type').value;
    document.getElementById('report_type').dispatchEvent(new Event('change'));
});

// Reset Filter
function resetFilter() {
    window.location.href = '{{ route("sale.report") }}';
}

// ================= CHART.JS CONFIGURATION ================= 
const ctx = document.getElementById('salesChart');
const salesData = @json($sales);

// Prepare data for chart
const labels = [];
const dataValues = [];

salesData.forEach(sale => {
    const date = new Date(sale.tanggal_transaksi);
    let label;
    
    @if($reportType == 'daily')
        label = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    @elseif($reportType == 'custom')
        label = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    @elseif($reportType == 'monthly')
        label = date.toLocaleDateString('id-ID', { day: 'numeric' });
    @else
        label = date.toLocaleDateString('id-ID', { month: 'short' });
    @endif
    
    labels.push(label);
    dataValues.push(sale.total_belanja);
});

const salesChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Total Penjualan (Rp)',
            data: dataValues,
            backgroundColor: 'rgba(99, 102, 241, 0.6)',
            borderColor: 'rgb(99, 102, 241)',
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    font: {
                        family: 'Plus Jakarta Sans',
                        size: 13,
                        weight: '600'
                    },
                    padding: 15,
                    usePointStyle: true
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: {
                    size: 14,
                    family: 'Plus Jakarta Sans',
                    weight: '600'
                },
                bodyFont: {
                    size: 13,
                    family: 'Plus Jakarta Sans'
                },
                callbacks: {
                    label: function(context) {
                        return 'Total: Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + (value / 1000) + 'k';
                    },
                    font: {
                        family: 'Plus Jakarta Sans',
                        size: 11
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)',
                    drawBorder: false
                }
            },
            x: {
                ticks: {
                    font: {
                        family: 'Plus Jakarta Sans',
                        size: 11
                    }
                },
                grid: {
                    display: false,
                    drawBorder: false
                }
            }
        }
    }
});

// ================= EXPORT TO PDF ================= 
function exportToPDF() {
    window.print();
}
</script>

<style>
/* ================= FILTER SECTION ================= */
.filter-section {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
}

.filter-row {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group-filter {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group-filter label {
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.form-control-select,
.form-control-input {
    padding: 12px 16px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    color: #374151;
    background: white;
    transition: all 0.3s ease;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.form-control-select:focus,
.form-control-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-group-filter-range {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.date-range-group {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 16px;
    align-items: end;
}

.date-input-wrapper {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.date-separator {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6366f1;
    font-size: 20px;
    padding-bottom: 12px;
}

.filter-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-filter,
.btn-export,
.btn-reset {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.btn-filter {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: white;
}

.btn-export {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.btn-reset {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
}

.btn-filter:hover,
.btn-export:hover,
.btn-reset:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* ================= PERIOD INFO ================= */
.period-info {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-left: 4px solid #3b82f6;
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    color: #1e40af;
}

.period-info i {
    font-size: 20px;
}

.period-info strong {
    color: #1e3a8a;
    font-weight: 700;
}

/* ================= STATS CARDS ================= */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
    border-left: 4px solid;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.stat-primary { border-left-color: #6366f1; }
.stat-success { border-left-color: #10b981; }
.stat-info { border-left-color: #3b82f6; }
.stat-warning { border-left-color: #f59e0b; }

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.stat-primary .stat-icon {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: white;
}

.stat-success .stat-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.stat-info .stat-icon {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.stat-warning .stat-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-content h3 {
    font-size: 14px;
    color: #6b7280;
    margin: 0 0 8px 0;
    font-weight: 500;
}

.stat-value {
    font-size: 24px;
    font-weight: 800;
    color: #111827;
    margin: 0 0 4px 0;
}

.stat-label {
    font-size: 12px;
    color: #9ca3af;
    font-weight: 500;
}

/* ================= CHART SECTION ================= */
.chart-section {
    margin-bottom: 24px;
}

.chart-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.chart-card h3 {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 24px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.chart-card h3 i {
    color: #6366f1;
}

/* ================= TABLE CELLS ================= */
.date-cell,
.user-cell,
.customer-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.date-cell i,
.user-cell i,
.customer-cell i {
    color: #6366f1;
    font-size: 16px;
}

.date-main {
    font-weight: 600;
    color: #111827;
    font-size: 14px;
}

.date-time {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
}

.price-text {
    color: #059669;
    font-weight: 700;
    font-size: 15px;
}

.pay-text {
    color: #3b82f6;
    font-weight: 600;
}

.change-text {
    color: #f59e0b;
    font-weight: 600;
}

.badge-success {
    padding: 6px 12px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.table-footer {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    font-weight: 600;
}

.table-footer td {
    border-top: 3px solid #6366f1;
    padding: 16px;
}

.total-highlight {
    color: #6366f1;
    font-size: 16px;
}

.text-right {
    text-align: right;
}

/* ================= RESPONSIVE ================= */
@media (max-width: 768px) {
    .stats-container {
        grid-template-columns: 1fr;
    }
    
    .date-range-group {
        grid-template-columns: 1fr;
    }
    
    .date-separator {
        transform: rotate(90deg);
        padding: 0;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .btn-filter,
    .btn-export,
    .btn-reset {
        width: 100%;
        justify-content: center;
    }
}

/* ================= PRINT STYLES ================= */
@media print {
    .sidebar,
    .navbar,
    .sidebar-overlay,
    .filter-section,
    .filter-actions {
        display: none !important;
    }
    
    .main-content {
        padding: 0;
    }
    
    .stat-card {
        break-inside: avoid;
    }
}
</style>

</body>
</html>