@yield('content')

<head>
    <!-- Link CDN untuk Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<div class="sidebar1">
    <div class="user-info">
        <img src="https://www.w3schools.com/w3images/avatar2.png" alt="User" class="user-avatar">
        <p>{{ Auth::user()->nama_user }}</p> <!-- Mengambil nama_user dari pengguna yang login -->
    </div>
    <nav>
        <ul>
            <li><a href="{{ route('dashboard_kasir') }}"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li><a href="{{ route('brands.index') }}"><i class="bi bi-cart-fill"></i> Transaksi</a></li>
            <li><a href="{{ route('categories.index') }}"><i class="bi bi-clock-history"></i> History</a></li>
        </ul>
    </nav>
</div>
