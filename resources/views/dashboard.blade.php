<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sistem Inventory dan Kasir</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>        
</head>
<body>
    @include('layouts.navbar')
    @include('layouts.sidebar')
    
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <main class="main-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <h1 class="welcome-title">Selamat Datang, {{ Auth::user()->nama_user }}! 👋</h1>
                <p class="welcome-subtitle">Berikut ringkasan bisnis Anda hari ini</p>
            </div>
            <div class="welcome-date">
                <i class="bi bi-calendar3"></i>
                <span id="currentDate"></span>
            </div>
        </div>

        <!-- Statistik Cards -->
        <div class="stats-grid">
            <!-- Card 1: Transaksi Hari Ini -->
            <div class="stat-card stat-card-blue">
                <div class="stat-icon">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Transaksi Hari Ini</h3>
                    <p class="stat-value">{{ $transaksiHariIni }}</p>
                    <p class="stat-desc">Transaksi</p>
                </div>
            </div>
            
            <!-- Card 2: Penjualan Hari Ini -->
            <div class="stat-card stat-card-green">
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Penjualan Hari Ini</h3>
                    <p class="stat-value">Rp {{ number_format($penjualanHariIni / 1000, 0) }}K</p>
                    <p class="stat-desc">Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}</p>
                </div>
            </div>
            
            <!-- Card 3: Keuntungan Hari Ini -->
            <div class="stat-card stat-card-purple">
                <div class="stat-icon">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Keuntungan Hari Ini</h3>
                    <p class="stat-value">Rp {{ number_format($keuntunganHariIni / 1000, 0) }}K</p>
                    <p class="stat-desc">Rp {{ number_format($keuntunganHariIni, 0, ',', '.') }}</p>
                </div>
            </div>
            
            <!-- Card 4: Total Barang Diskon -->
            <div class="stat-card stat-card-orange">
                <div class="stat-icon">
                    <i class="bi bi-tag-fill"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Promo Diskon</h3>
                    <p class="stat-value">{{ $barangDiskonHariIni }}</p>
                    <p class="stat-desc">Barang</p>
                </div>
            </div>
        </div>

        <!-- Grafik Penjualan -->
        <div class="chart-section">
            <div class="section-header">
                <div class="section-title">
                    <i class="bi bi-bar-chart-line-fill"></i>
                    <h3>Grafik Penjualan</h3>
                </div>
                <span class="section-badge">7 Hari Terakhir</span>
            </div>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Produk Promo/Diskon -->
        <div class="promo-section">
            <div class="section-header">
                <div class="section-title">
                    <i class="bi bi-gift-fill"></i>
                    <h3>Produk dengan Stok Tinggi</h3>
                </div>
                <span class="section-badge">Rekomendasi Promo</span>
            </div>
            <div class="table-responsive">
                <table class="promo-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Stok</th>
                            <th>Harga Modal</th>
                            <th>Harga Jual</th>
                            <th>Margin</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($promoItems as $item)
                        <tr>
                            <td><span class="id-badge">#{{ $item->id }}</span></td>
                            <td class="product-name">{{ $item->nama_produk }}</td>
                            <td><strong class="stock-number">{{ $item->stok }}</strong></td>
                            <td>Rp {{ number_format($item->modal, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $margin = $item->harga_jual - $item->modal;
                                    $persenMargin = $item->modal > 0 ? ($margin / $item->modal) * 100 : 0;
                                @endphp
                                <span class="margin-badge">{{ number_format($persenMargin, 1) }}%</span>
                            </td>
                            <td>
                                @if($item->stok > 50)
                                    <span class="status-badge status-high">Stok Sangat Tinggi</span>
                                @elseif($item->stok > 20)
                                    <span class="status-badge status-medium">Stok Tinggi</span>
                                @else
                                    <span class="status-badge status-normal">Stok Normal</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Toggle Sidebar (mobile)
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }

        // Set current date
        const dateElement = document.getElementById('currentDate');
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateElement.textContent = new Date().toLocaleDateString('id-ID', options);

        // Chart.js Configuration
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($salesLabels),
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: @json($salesData),
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#0ea5e9',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#0284c7',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 13,
                                family: "'Plus Jakarta Sans', sans-serif",
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        borderRadius: 8,
                        titleFont: {
                            size: 13,
                            family: "'Plus Jakarta Sans', sans-serif",
                            weight: '600'
                        },
                        bodyFont: {
                            size: 12,
                            family: "'Plus Jakarta Sans', sans-serif"
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Penjualan: Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                family: "'Plus Jakarta Sans', sans-serif"
                            },
                            callback: function(value) {
                                return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'K';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                family: "'Plus Jakarta Sans', sans-serif"
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>