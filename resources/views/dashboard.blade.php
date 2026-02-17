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
    
    <style>
        /* ============================================ */
        /* FSN ANALYSIS STYLES */
        /* ============================================ */
        
        /* FSN Badges */
        .fsn-badge {
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .fsn-fast {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }

        .fsn-slow {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
        }

        .fsn-non-moving {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        /* Discount Badge */
        .discount-badge {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);
        }

        .no-discount {
            color: #9ca3af;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Days Badge */
        .days-badge {
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .days-critical {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .days-warning {
            background: #fef3c7;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        .days-normal {
            background: #e0e7ff;
            color: #6366f1;
            border: 1px solid #c7d2fe;
        }

        /* FSN Info Box */
        .fsn-info-box {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 24px;
            padding: 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .fsn-info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.875rem;
            color: #475569;
            font-weight: 500;
        }

        .fsn-info-item i {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Empty State */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 16px;
        }

        .empty-state p {
            color: #94a3b8;
            font-size: 1rem;
            margin: 0;
        }

        /* Price Highlight */
        .price-highlight {
            font-weight: 700;
            color: #059669;
            font-size: 0.95rem;
        }

        /* Stock Number Enhancement */
        .stock-number {
            color: #0ea5e9;
            font-size: 1.1rem;
        }

        /* Section Header Enhancement */
        .section-header .section-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.25);
        }

        /* Table Enhancement for FSN */
        .promo-table tbody tr:hover {
            background: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .fsn-info-box {
                flex-direction: column;
                gap: 12px;
            }
            
            .fsn-info-item {
                font-size: 0.8rem;
            }
            
            .days-badge {
                font-size: 0.75rem;
                padding: 4px 10px;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .promo-section {
            animation: fadeIn 0.5s ease-in-out;
        }
    </style>
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

        <!-- Produk Non-Moving dengan Diskon (FSN Analysis) -->
        <div class="promo-section">
            <div class="section-header">
                <div class="section-title">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <h3>Produk Diskon</h3>
                </div>
                <span class="section-badge">Analisis FSN</span>
            </div>
            <div class="table-responsive">
                <table class="promo-table">
                    <thead>
                        <tr>
                            <th>ID Produk</th>
                            <th>Nama Produk</th>
                            <th>Stok</th>
                            <th>Harga Modal</th>
                            <th>Harga Jual</th>
                            <th>Diskon</th>
                            <th>Harga Setelah Diskon</th>
                            <th>Kategori FSN</th>
                            <th>Umur Barang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nonMovingItems as $item)
                        <tr>
                            <td><span class="id-badge">#{{ $item->id_produk }}</span></td>
                            <td class="product-name">{{ $item->nama_produk }}</td>
                            <td><strong class="stock-number">{{ $item->stok }}</strong></td>
                            <td>Rp {{ number_format($item->modal, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                            <td>
                                @if($item->diskon > 0)
                                    <span class="discount-badge">{{ number_format($item->diskon, 0) }}%</span>
                                @else
                                    <span class="no-discount">0%</span>
                                @endif
                            </td>
                            <td>
                                <strong class="price-highlight">Rp {{ number_format($item->harga_setelah_diskon, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <span class="fsn-badge fsn-non-moving">Non-Moving</span>
                            </td>
                            <td>
                                @php
                                    $days = $item->days_not_sold ?? $item->umur_hari;
                                    $badgeClass = $days > 90 ? 'days-critical' : ($days > 60 ? 'days-warning' : 'days-normal');
                                @endphp
                                <span class="days-badge {{ $badgeClass }}">
                                    <i class="bi bi-clock"></i>
                                    {{ $days }} hari
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Tidak ada produk non-moving dengan diskon saat ini</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
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