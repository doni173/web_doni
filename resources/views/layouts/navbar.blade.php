@yield('content')

<nav class="navbar-container">
    <!-- Hamburger Button (muncul di mobile) -->
    <button class="hamburger" onclick="toggleSidebar()" aria-label="Toggle Menu">
        <i class="bi bi-list"></i>
    </button>

    <div class="navbar-brand">
        <div class="brand-icon">
            <i class="bi bi-shop"></i>
        </div>
        <div class="brand-text">
            <h1 class="brand-title">Sistem Inventory & Kasir</h1>
            <span class="brand-subtitle">DTC MULTIMEDIA</span>
        </div>
    </div>

    <!-- User Actions -->
    <div class="navbar-actions">
        <div class="user-info-nav">
            <i class="bi bi-person-circle"></i>
            <span class="user-name">{{ Auth::user()->nama_user }}</span>
        </div>
        
        <!-- Logout Form -->
        <form action="{{ route('logout') }}" method="POST" class="logout-form">
            @csrf
            @method('POST')
            <button type="submit" class="btn-logout" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
                <span class="logout-text">Keluar</span>
            </button>
        </form>
    </div>
</nav>