@yield('content')

<aside class="fixed top-0 left-0 h-screen w-[175px] bg-white shadow-lg z-50 flex flex-col">
    <!-- User Info Section -->
    <div class="p-4 border-b border-gray-200">
        <div class="flex flex-col items-center">
            <img src="https://www.w3schools.com/w3images/avatar2.png" alt="User" class="w-16 h-16 rounded-full mb-2 border-2 border-gray-200">
            <p class="text-sm font-semibold text-gray-900 text-center">{{ Auth::user()->nama_user }}</p>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-4">
        <!-- Admin Menu -->
        <div class="mb-6">
            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Admin</p>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded-lg transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-primary-50 text-primary-600' : '' }}">
                        <i class="bi bi-house-door text-lg"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded-lg transition-colors duration-200 {{ request()->routeIs('categories.*') ? 'bg-primary-50 text-primary-600' : '' }}">
                        <i class="bi bi-tag text-lg"></i>
                        <span>Kategori</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('brands.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded-lg transition-colors duration-200 {{ request()->routeIs('brands.*') ? 'bg-primary-50 text-primary-600' : '' }}">
                        <i class="bi bi-box-seam text-lg"></i>
                        <span>Brand</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('items.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded-lg transition-colors duration-200 {{ request()->routeIs('items.*') ? 'bg-primary-50 text-primary-600' : '' }}">
                        <i class="bi bi-box text-lg"></i>
                        <span>Barang</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('services.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded-lg transition-colors duration-200 {{ request()->routeIs('services.*') ? 'bg-primary-50 text-primary-600' : '' }}">
                        <i class="bi bi-gear text-lg"></i>
                        <span>Service</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded-lg transition-colors duration-200 {{ request()->routeIs('users.*') ? 'bg-primary-50 text-primary-600' : '' }}">
                        <i class="bi bi-person text-lg"></i>
                        <span>Pengguna</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('sale.report') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded-lg transition-colors duration-200 {{ request()->routeIs('sale.report') ? 'bg-primary-50 text-primary-600' : '' }}">
                        <i class="bi bi-file-earmark-spreadsheet text-lg"></i>
                        <span>Laporan</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Kasir Menu -->
        <div>
            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Kasir</p>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard_kasir') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded-lg transition-colors duration-200 {{ request()->routeIs('dashboard_kasir') ? 'bg-primary-50 text-primary-600' : '' }}">
                        <i class="bi bi-house-door text-lg"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('sale.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded-lg transition-colors duration-200 {{ request()->routeIs('sale.index') ? 'bg-primary-50 text-primary-600' : '' }}">
                        <i class="bi bi-cart-fill text-lg"></i>
                        <span>Transaksi</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('sale.history') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded-lg transition-colors duration-200 {{ request()->routeIs('sale.history') ? 'bg-primary-50 text-primary-600' : '' }}">
                        <i class="bi bi-clock-history text-lg"></i>
                        <span>History</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</aside>  