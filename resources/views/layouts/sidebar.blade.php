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
            <!-- Master Data Section -->
            <div class="sidebar-section"><li>Master Data</li></div>
            <ul>
                <li><a href="{{ route('categories.index') }}"><i class="bi bi-tag"></i>Kategori</a></li>
                <li><a href="{{ route('brands.index') }}"><i class="bi bi-box-seam"></i>Brand</a></li>
                <li><a href="{{ route('items.index') }}"><i class="bi bi-box"></i>Barang</a></li>
                <li><a href="{{ route('services.index') }}"><i class="bi bi-gear"></i>Service</a></li>
                <li><a href="{{ route('users.index') }}"><i class="bi bi-person"></i>User </a></li>
            </ul>

            <!-- Report Section -->
            <div class="sidebar-section"><li>Laporan</li></div>
            <ul>
                <li><a href="{{ route('sales.report') }}"><i class="bi bi-file-earmark-spreadsheet"></i>Laporan</a></li> <!-- Placeholder for route -->
            </ul>

            <!-- Transaksi Section -->
            <div class="sidebar-section"><li>Transaksi</li></div>
            <ul>
                <li><a href="{{ route('sale.index') }}"><i class="bi bi-cart-fill"></i>Jual</a></li>
                <li><a href="{{ route('purchase.index') }}"><i class="bi bi-cart-check"></i>Beli</a></li> <!-- Placeholder for route -->
                
            </ul>

            <!-- History Section -->
            <div class="sidebar-section"><li>Riwayat</li></div>
            <ul>
                <li><a href="{{ route('sale.history') }}"><i class="bi bi-clock-history"></i>jual</a></li>
                <li><a href="#"><i class="bi bi-clock"></i>Beli</a></li> <!-- Placeholder for route -->
                
            </ul>
        </ul>
    </nav>
</div>
