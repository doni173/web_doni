@yield('content')

<nav class="fixed top-0 left-[175px] right-0 h-14 bg-white shadow-md z-40 flex items-center justify-between px-6">
    <div>
        <h2 class="text-lg font-bold text-gray-900">Sistem Inventory dan Kasir | DTC MULTIMEDIA</h2>
    </div>

    <!-- Logout Form -->
    <div class="flex items-center">
        <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            @method('POST')
            <button type="submit" class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                <i class="bi bi-box-arrow-right text-xl"></i>
                <span class="text-sm font-medium">Logout</span>
            </button>
        </form>
    </div>
</nav>
