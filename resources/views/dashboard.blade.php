<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sistem Inventory dan Kasir</title>
    @vite(['resources/css/app.css'])
    <!-- Link CDN untuk Bootstrap Icons Doni dan Shella -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Menambahkan Chart.js untuk grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

@include('layouts.sidebar')
@include('layouts.navbar')

<body class="bg-gray-50 font-sans">
    <div class="ml-[175px] mt-10 p-6">
        <!-- Stat Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Card 1: Transaksi Hari Ini -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 mb-2">Transaksi Hari Ini</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $transactionsToday }}</p>
                    </div>
                    <div class="bg-primary-100 rounded-full p-3">
                        <i class="bi bi-receipt-cutoff text-primary-500 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Card 2: Penjualan Hari Ini -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 mb-2">Penjualan Hari Ini</p>
                        <p class="text-3xl font-bold text-gray-900">Rp {{ number_format($salesToday, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="bi bi-cash-stack text-green-500 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Card 3: Keuntungan Hari Ini -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 mb-2">Keuntungan Hari Ini</p>
                        <p class="text-3xl font-bold text-gray-900">Rp {{ number_format($profitToday, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="bi bi-graph-up-arrow text-blue-500 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Card 4: Promo Diskon -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 mb-2">Promo Diskon</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $promoCount }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="bi bi-tag text-purple-500 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section (Grafik Penjualan) -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Grafik Penjualan</h3>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Promo Diskon Table Section -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Promo Diskon</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diskon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">-</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Pastikan DOM sudah loaded sebelum membuat chart
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('salesChart');
            if (ctx) {
                var salesChart = new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                        datasets: [{
                            label: 'Penjualan',
                            data: [1000000, 1500000, 2000000, 2500000, 3000000, 3500000, 4000000],
                            borderColor: 'rgba(65, 152, 251, 1)',
                            backgroundColor: 'rgba(65, 152, 251, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgba(65, 152, 251, 1)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
