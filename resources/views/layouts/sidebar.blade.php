<aside class="sidebar">
    <!-- User Profile Section -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <img src="https://www.w3schools.com/w3images/avatar2.png" alt="User Avatar">
            <div class="user-status"></div>
        </div>
        <div class="user-details">
            <h3 class="user-name">{{ Auth::user()->name }}</h3>
            <p class="user-role">{{ ucfirst(Auth::user()->role) }}</p>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">

        {{-- ================================================
             SIDEBAR ADMIN
        ================================================= --}}
        @if(Auth::check() && Auth::user()->role === 'admin')

            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-speedometer2"></i></div>
                <span class="nav-label">Dashboard</span>
            </a>

            <div class="nav-section"><span class="section-title">Master Data</span></div>

            <a href="{{ route('categories.index') }}"
               class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-grid-3x3-gap"></i></div>
                <span class="nav-label">Kategori</span>
            </a>

            <a href="{{ route('brands.index') }}"
               class="nav-item {{ request()->routeIs('brands.*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-bookmark-star"></i></div>
                <span class="nav-label">Brand</span>
            </a>

            <a href="{{ route('items.index') }}"
               class="nav-item {{ request()->routeIs('items.*') || request()->routeIs('fsn.*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-box-seam"></i></div>
                <span class="nav-label">Barang</span>
            </a>

            <a href="{{ route('services.index') }}"
               class="nav-item {{ request()->routeIs('services.*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-tools"></i></div>
                <span class="nav-label">Service</span>
            </a>

            <a href="{{ route('suppliers.index') }}"
               class="nav-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-truck"></i></div>
                <span class="nav-label">Supplier</span>
            </a>

            <a href="{{ route('users.index') }}"
               class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-people"></i></div>
                <span class="nav-label">User</span>
            </a>

            <div class="nav-section"><span class="section-title">Laporan</span></div>

            <a href="{{ route('sale.report') }}"
               class="nav-item {{ request()->routeIs('sale.report') || request()->routeIs('stock.report') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
                <span class="nav-label">Laporan</span>
            </a>

            <div class="nav-section"><span class="section-title">Transaksi</span></div>

            <a href="{{ route('sale.index') }}"
               class="nav-item {{ request()->routeIs('sale.index') || request()->routeIs('sale.store') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-cart-check-fill"></i></div>
                <span class="nav-label">Penjualan</span>
            </a>

            <a href="{{ route('purchase.index') }}"
               class="nav-item {{ request()->routeIs('purchase.index') || request()->routeIs('purchase.store') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-bag-plus-fill"></i></div>
                <span class="nav-label">Pembelian</span>
            </a>

            <div class="nav-section"><span class="section-title">Riwayat</span></div>

            <a href="{{ route('sale.history') }}"
               class="nav-item {{ request()->routeIs('sale.history') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-clock-history"></i></div>
                <span class="nav-label">Riwayat Jual</span>
            </a>

            <a href="{{ route('purchase.history') }}"
               class="nav-item {{ request()->routeIs('purchase.history') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-clock"></i></div>
                <span class="nav-label">Riwayat Beli</span>
            </a>

        {{-- ================================================
             SIDEBAR KASIR
        ================================================= --}}
        @elseif(Auth::check() && Auth::user()->role === 'kasir')

            <a href="{{ route('dashboard_kasir') }}"
               class="nav-item {{ request()->routeIs('dashboard_kasir') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-speedometer2"></i></div>
                <span class="nav-label">Dashboard</span>
            </a>

            <div class="nav-section"><span class="section-title">Transaksi</span></div>

            <a href="{{ route('sale.index') }}"
               class="nav-item {{ request()->routeIs('sale.index') || request()->routeIs('sale.store') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-cart-check-fill"></i></div>
                <span class="nav-label">Penjualan</span>
            </a>

            <div class="nav-section"><span class="section-title">Riwayat</span></div>

            <a href="{{ route('sale.history') }}"
               class="nav-item {{ request()->routeIs('sale.history') ? 'active' : '' }}">
                <div class="nav-icon"><i class="bi bi-clock-history"></i></div>
                <span class="nav-label">Riwayat Penjualan</span>
            </a>

        @endif

    </nav>
</aside>