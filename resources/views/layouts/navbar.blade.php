@yield('content')

<div class="navbar-container">
    <h2>Sistem Inventory dan Kasir<br>DTC MULTIMEDIA<h2>

    <!-- Logout Form -->
     <div class="logout">
        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
            @csrf
            @method('POST')
            <button type="submit" class="logout" style="background:none; border:none; color: inherit; cursor: pointer;">
                <i class="bi bi-box-arrow-right"></i> <!-- Ikon logout -->
            </button>
        </form>
    </div>
</div>
