@yield('content')
<head>
    <!-- Link CDN untuk Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<div class="sidebar">
    <div class="user-info">
        <img src="https://www.w3schools.com/w3images/avatar2.png" alt="User" class="user-avatar">
         <p>  {{ Auth::user()->nama_user }}</p> <!-- Mengambil nama_user dari pengguna yang login -->
    </div>
    <nav>
        <ul>
            <li><a href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i>Dashboard</a></li>
            <li><a href="{{ route('categories.index') }}"><i class="bi bi-tag"></i>Kategori</a></li>
            <li><a href="{{ route('brands.index') }}"><i class="bi bi-box-seam"></i>Brand</a></li>
            <li><a href="{{ route('items.index') }}"><i class="bi bi-box"></i>Barang</a></li>
            <li><a href="{{ route('services.index') }}"><i class="bi bi-gear"></i>Service</a></li>
            <li><a href="{{ route('users.index') }}"><i class="bi bi-person"></i>Pengguna</a></li>
            <li><a href="{{ route('sale.report') }}"><i class="bi bi-file-earmark-spreadsheet"></i>Laporan</a></li>
        </ul>

        <ul>
            <li><a href="{{ route('dashboard_kasir') }}"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li><a href="{{ route('sale.index') }}"><i class="bi bi-cart-fill"></i> Transaksi</a></li>
            <li><a href="{{ route('sale.history') }}"><i class="bi bi-clock-history"></i> History</a></li>
        </ul>
    </nav>
</div>  