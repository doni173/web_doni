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

@include('layouts_kasir.sidebar')
@include('layoutsk_kasirnavbar')

<body>
    <br>
    <div class="container-all">
        <div class="main-content">
            <!-- Stat Cards (4 Kartu Statistik) -->
            <div class="stat-cards">
                <div class="stat-card">
                    <h3>Transaksi Hari Ini</h3>
                    <p>{{ $transactionsToday }}</p>
                </div>
                <div class="stat-card">
                    <h3>Penjualan Hari Ini</h3>
                    <p></p>
                </div>
                <div class="stat-card">
                    <h3>Keuntungan Hari Ini</h3>
                    <p></p>
                </div>
                <div class="stat-card">
                    <h3>Promo Diskon</h3>
                    <p>{{ $promoCount }}</p>
                </div>
            </div>

            <!-- Grafik Penjualan -->
            <div class="grafik-penjualan">
                
            </div>

            <!-- Tabel Promo Diskon -->
            <div class="promo-diskon">
                <h3>Promo Diskon</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Merk</th>
                            <th>Diskon</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Produk A</td>
                            <td>Merk A</td>
                            <td>10%</td>
                            <td>Aktif</td>
                        </tr>
                        <tr>
                            <td>Produk B</td>
                            <td>Merk B</td>
                            <td>15%</td>
                            <td>Non-Aktif</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Data untuk grafik penjualan
        var ctx = document.getElementById('salesChart').getContext('2d');
        var salesChart = new Chart(ctx, {
            type: 'line',  // Jenis grafik (garis)
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],  // Hari
                datasets: [{
                    label: 'Penjualan',
                    data: [1000000, 1500000, 2000000, 2500000, 3000000, 3500000, 4000000],  // Data penjualan
                    borderColor: 'rgba(75, 192, 192, 1)',  // Warna garis grafik
                    fill: false
                }]
            }
        });
    </script>

</body>

</html>
