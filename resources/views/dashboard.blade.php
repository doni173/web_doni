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
<br>
    <div class="container">

            <div class="stat-cards">
                <div class="stat-card">
                   
                </div>
                <div class="stat-card">
                    
                </div>
                <div class="stat-card">
                    
                </div>
                <div class="stat-card">
                    
                </div>
            </div>

            <!-- Grafik Penjualan -->
            <div class="grafik-penjualan">
                
            </div>
            <!-- Tabel Promo Diskon -->
            <div class="promo-diskon">
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
