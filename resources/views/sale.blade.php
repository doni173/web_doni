<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Penjualan | Sistem Inventory dan Kasir</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

@include('layouts.navbar')
@include('layouts.sidebar')
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="main-container">
    <div class="main-content">

        <h2>Transaksi Penjualan</h2>

        <!-- ================= PRODUCTS & SERVICES GRID ================= -->
        <div class="products-services-grid">
            
            <!-- ================= PRODUCT SECTION ================= -->
            <div class="section-wrapper">
                <h3 class="section-title">
                    <i class="bi bi-box-seam"></i> Daftar Produk
                </h3>
                
                <div class="search-wrapper">
                    <div class="search-input-wrapper">
                        <input type="text" 
                               class="form-control" 
                               placeholder="Cari produk..." 
                               id="searchProduct"
                               autocomplete="off">
                        <span class="search-icon">
                            <i class="bi bi-search"></i>
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table-main">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="productTableBody">
                            @forelse($items as $item)
                            <tr data-product-search="{{ strtolower($item->nama_produk) }}">
                                <td data-label="Produk">
                                    <span class="product-name">{{ $item->nama_produk }}</span>
                                </td>
                                <td data-label="Harga">
                                    Rp {{ number_format($item->harga_setelah_diskon, 0, ',', '.') }}
                                </td>
                                <td data-label="Stok">
                                    <span class="badge {{ $item->stok == 0 ? 'badge-danger' : ($item->stok < 10 ? 'badge-warning' : 'badge-success') }}">
                                        {{ $item->stok }} unit
                                    </span>
                                </td>
                                <td data-label="Aksi">
                                    <button class="btn-add-cart {{ $item->stok == 0 ? 'disabled' : '' }}" 
                                            data-id="{{ $item->id_produk }}"
                                            data-nama="{{ $item->nama_produk }}"
                                            data-harga="{{ $item->harga_setelah_diskon }}"
                                            data-diskon="{{ $item->diskon }}"
                                            data-stok="{{ $item->stok }}"
                                            data-type="produk"
                                            {{ $item->stok == 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-{{ $item->stok == 0 ? 'x-circle' : 'plus-circle' }}"></i>
                                        {{ $item->stok == 0 ? 'Stok Habis' : 'Tambah' }}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <p>Tidak ada data produk</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ================= SERVICE SECTION ================= -->
            <div class="section-wrapper">
                <h3 class="section-title">
                    <i class="bi bi-gear"></i> Daftar Service
                </h3>
                
                <div class="search-wrapper">
                    <div class="search-input-wrapper">
                        <input type="text" 
                               class="form-control" 
                               placeholder="Cari service..." 
                               id="searchService"
                               autocomplete="off">
                        <span class="search-icon">
                            <i class="bi bi-search"></i>
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table-main">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="serviceTableBody">
                            @forelse($services as $service)
                            <tr data-service-search="{{ strtolower($service->service) }}">
                                <td data-label="Service">
                                    <span class="product-name">{{ $service->service }}</span>
                                </td>
                                <td data-label="Harga">
                                    Rp {{ number_format($service->harga_setelah_diskon, 0, ',', '.') }}
                                </td>
                                <td data-label="Aksi">
                                    <button class="btn-add-cart" 
                                            data-id="{{ $service->id_service }}"
                                            data-nama="{{ $service->service }}"
                                            data-harga="{{ $service->harga_setelah_diskon }}"
                                            data-diskon="{{ $service->diskon }}"
                                            data-type="service">
                                        <i class="bi bi-plus-circle"></i>
                                        Tambah
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <p>Tidak ada data service</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <!-- ================= END PRODUCTS & SERVICES GRID ================= -->

        <!-- ================= CART SECTION ================= -->
        <div class="section-wrapper cart-section">
            <h3 class="section-title">
                <i class="bi bi-cart"></i> Keranjang Belanja
            </h3>

            <div class="table-responsive">
                <table class="table-main">
                    <thead>
                        <tr>
                            <th>Produk / Service</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Diskon</th>
                            <th>Harga Setelah Diskon</th>
                            <th>Total Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="empty-state">
                                    <i class="bi bi-cart-x"></i>
                                    <p>Keranjang belanja masih kosong</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ================= CHECKOUT FORM ================= -->
            <div class="checkout-section">
                <form action="{{ route('sale.store') }}" method="POST" id="checkoutForm">
                    @csrf
                    
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="tanggal_transaksi">
                                <i class="bi bi-calendar"></i> Tanggal Transaksi
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="tanggal_transaksi" 
                                   name="tanggal_transaksi" 
                                   value="{{ date('Y-m-d') }}" 
                                   required>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="nama_pelanggan">
                                <i class="bi bi-person"></i> Nama Pelanggan
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nama_pelanggan" 
                                   name="nama_pelanggan" 
                                   placeholder="Masukkan nama pelanggan" 
                                   required>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="jumlah_bayar">
                                <i class="bi bi-cash"></i> Jumlah Bayar
                            </label>
                            <input type="text" 
                                   class="form-control rupiah" 
                                   id="jumlah_bayar" 
                                   name="jumlah_bayar" 
                                   placeholder="Masukkan jumlah bayar" 
                                   autocomplete="off" 
                                   required>
                        </div>
                    </div>

                    <div class="summary-section">
                        <div class="summary-item">
                            <span class="summary-label">Total Belanja:</span>
                            <span class="summary-value" id="totalBelanja">Rp 0</span>
                        </div>
                        <div class="summary-item kembalian">
                            <span class="summary-label">Kembalian:</span>
                            <span class="summary-value" id="kembalian">Rp 0</span>
                        </div>
                    </div>

                    <input type="hidden" name="total_harga" id="totalHargaInput" value="0">
                    <div id="cartItemsContainer"></div>

                    <div class="checkout-actions">
                        <button type="button" class="btn-clear-cart" onclick="clearCart()">
                            <i class="bi bi-trash"></i> Kosongkan Keranjang
                        </button>
                        <button type="submit" class="btn-checkout" id="btnCheckout" disabled>
                            <i class="bi bi-check-circle"></i> Selesaikan Penjualan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- ================= SWEETALERT ================= -->
@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: '{{ session('success') }}',
    timer: 2000,
    showConfirmButton: false
});
</script>
@endif

@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: '{{ session('error') }}',
    timer: 2000,
    showConfirmButton: false
});
</script>
@endif

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ================= GLOBAL VARIABLES ================= 
let cart = [];
let totalBelanja = 0;

// ================= SIDEBAR TOGGLE ================= 
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('active');
}

// ================= SEARCH FUNCTIONALITY ================= 
$(document).ready(function() {
    // Search Product
    $('#searchProduct').on('input', function() {
        const query = $(this).val().toLowerCase().trim();
        $('#productTableBody tr').each(function() {
            const productName = $(this).data('product-search');
            if (productName && productName.includes(query)) {
                $(this).show();
            } else if (productName) {
                $(this).hide();
            }
        });
    });

    // Search Service
    $('#searchService').on('input', function() {
        const query = $(this).val().toLowerCase().trim();
        $('#serviceTableBody tr').each(function() {
            const serviceName = $(this).data('service-search');
            if (serviceName && serviceName.includes(query)) {
                $(this).show();
            } else if (serviceName) {
                $(this).hide();
            }
        });
    });

    // ================= ADD TO CART ================= 
    $(document).on('click', '.btn-add-cart:not(.disabled)', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const harga = parseFloat($(this).data('harga'));
        const diskon = parseFloat($(this).data('diskon'));
        const type = $(this).data('type');
        const stok = $(this).data('stok');

        // Check if item already in cart
        const existingItem = cart.find(item => item.id === id && item.type === type);
        
        if (existingItem) {
            if (type === 'produk' && existingItem.jumlah >= stok) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stok Tidak Cukup',
                    text: 'Jumlah melebihi stok yang tersedia!',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }
            existingItem.jumlah++;
        } else {
            cart.push({
                id: id,
                nama: nama,
                harga: harga,
                diskon: diskon,
                jumlah: 1,
                type: type,
                stok: stok || null
            });
        }

        updateCart();
        
        Swal.fire({
            icon: 'success',
            title: 'Ditambahkan',
            text: `${nama} ditambahkan ke keranjang`,
            timer: 1000,
            showConfirmButton: false
        });
    });

    // ================= UPDATE CART ================= 
    window.updateCart = function() {
        const tbody = $('#cartTableBody');
        tbody.empty();

        if (cart.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="7" class="text-center">
                        <div class="empty-state">
                            <i class="bi bi-cart-x"></i>
                            <p>Keranjang belanja masih kosong</p>
                        </div>
                    </td>
                </tr>
            `);
            totalBelanja = 0;
            updateSummary();
            $('#btnCheckout').prop('disabled', true);
            return;
        }

        totalBelanja = 0;

        cart.forEach((item, index) => {
            const hargaSetelahDiskon = item.harga;
            const totalHarga = hargaSetelahDiskon * item.jumlah;
            totalBelanja += totalHarga;

            const row = `
                <tr>
                    <td data-label="Produk / Service">
                        <span class="product-name">${item.nama}</span>
                        <span class="badge badge-info">${item.type === 'produk' ? 'Produk' : 'Service'}</span>
                    </td>
                    <td data-label="Harga">Rp ${formatRupiah(item.harga.toString())}</td>
                    <td data-label="Jumlah">
                        <div class="quantity-control">
                            <button type="button" class="btn-qty" onclick="decreaseQty(${index})">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" 
                                   class="qty-input" 
                                   value="${item.jumlah}" 
                                   min="1" 
                                   ${item.stok ? 'max="' + item.stok + '"' : ''}
                                   onchange="updateQty(${index}, this.value)">
                            <button type="button" class="btn-qty" onclick="increaseQty(${index})">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </td>
                    <td data-label="Diskon">${item.diskon}%</td>
                    <td data-label="Harga Setelah Diskon">Rp ${formatRupiah(hargaSetelahDiskon.toString())}</td>
                    <td data-label="Total Harga">
                        <strong>Rp ${formatRupiah(totalHarga.toString())}</strong>
                    </td>
                    <td data-label="Aksi">
                        <button class="btn-delete" onclick="removeFromCart(${index})" title="Hapus">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        updateSummary();
        $('#btnCheckout').prop('disabled', false);
    }

    // ================= QUANTITY CONTROLS ================= 
    window.increaseQty = function(index) {
        const item = cart[index];
        if (item.type === 'produk' && item.jumlah >= item.stok) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Tidak Cukup',
                text: 'Jumlah melebihi stok yang tersedia!',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        cart[index].jumlah++;
        updateCart();
    }

    window.decreaseQty = function(index) {
        if (cart[index].jumlah > 1) {
            cart[index].jumlah--;
            updateCart();
        } else {
            removeFromCart(index);
        }
    }

    window.updateQty = function(index, value) {
        const qty = parseInt(value);
        const item = cart[index];
        
        if (qty < 1) {
            removeFromCart(index);
            return;
        }
        
        if (item.type === 'produk' && qty > item.stok) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Tidak Cukup',
                text: 'Jumlah melebihi stok yang tersedia!',
                timer: 2000,
                showConfirmButton: false
            });
            updateCart();
            return;
        }
        
        cart[index].jumlah = qty;
        updateCart();
    }

    // ================= REMOVE FROM CART ================= 
    window.removeFromCart = function(index) {
        Swal.fire({
            title: 'Hapus Item?',
            text: 'Apakah Anda yakin ingin menghapus item ini dari keranjang?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                cart.splice(index, 1);
                updateCart();
                Swal.fire({
                    icon: 'success',
                    title: 'Dihapus',
                    text: 'Item berhasil dihapus dari keranjang',
                    timer: 1000,
                    showConfirmButton: false
                });
            }
        });
    }

    // ================= CLEAR CART ================= 
    window.clearCart = function() {
        if (cart.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Keranjang Kosong',
                text: 'Tidak ada item di keranjang',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        Swal.fire({
            title: 'Kosongkan Keranjang?',
            text: 'Semua item akan dihapus dari keranjang!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-trash"></i> Ya, Kosongkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                cart = [];
                updateCart();
                $('#jumlah_bayar').val('');
                Swal.fire({
                    icon: 'success',
                    title: 'Dikosongkan',
                    text: 'Keranjang berhasil dikosongkan',
                    timer: 1000,
                    showConfirmButton: false
                });
            }
        });
    }

    // ================= UPDATE SUMMARY ================= 
    function updateSummary() {
        $('#totalBelanja').text('Rp ' + formatRupiah(totalBelanja.toString()));
        $('#totalHargaInput').val(totalBelanja);
        calculateKembalian();
    }

    // ================= CALCULATE KEMBALIAN ================= 
    function calculateKembalian() {
        const jumlahBayar = parseFloat($('#jumlah_bayar').val().replace(/\./g, '')) || 0;
        const kembalian = jumlahBayar - totalBelanja;
        
        if (kembalian >= 0) {
            $('#kembalian').text('Rp ' + formatRupiah(kembalian.toString()));
            $('#kembalian').removeClass('text-danger').addClass('text-success');
        } else {
            $('#kembalian').text('Rp ' + formatRupiah(Math.abs(kembalian).toString()));
            $('#kembalian').removeClass('text-success').addClass('text-danger');
        }
    }

    // ================= JUMLAH BAYAR INPUT ================= 
    $('#jumlah_bayar').on('input', function() {
        calculateKembalian();
    });

    // ================= FORMAT RUPIAH ================= 
    $(document).on('input', '.rupiah', function() {
        let val = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(formatRupiah(val));
    });

    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // ================= FORM SUBMIT ================= 
    $('#checkoutForm').on('submit', function(e) {
        e.preventDefault();

        if (cart.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Silakan tambahkan produk atau service terlebih dahulu!',
                timer: 2000,
                showConfirmButton: false
            });
            return false;
        }

        const jumlahBayar = parseFloat($('#jumlah_bayar').val().replace(/\./g, '')) || 0;
        
        if (jumlahBayar < totalBelanja) {
            Swal.fire({
                icon: 'warning',
                title: 'Jumlah Bayar Kurang',
                text: 'Jumlah bayar tidak mencukupi!',
                timer: 2000,
                showConfirmButton: false
            });
            return false;
        }

        // Prepare cart items untuk dikirim ke controller
        $('#cartItemsContainer').empty();
        
        cart.forEach((item, index) => {
            // Hitung harga_jual asli sebelum diskon
            const hargaJual = item.diskon > 0 
                ? Math.round(item.harga / (1 - item.diskon / 100))
                : item.harga;
            
            $('#cartItemsContainer').append(`
                <input type="hidden" name="items[${index}][id]" value="${item.id}">
                <input type="hidden" name="items[${index}][nama]" value="${item.nama}">
                <input type="hidden" name="items[${index}][type]" value="${item.type}">
                <input type="hidden" name="items[${index}][harga_jual]" value="${hargaJual}">
                <input type="hidden" name="items[${index}][jumlah]" value="${item.jumlah}">
                <input type="hidden" name="items[${index}][diskon]" value="${item.diskon}">
                <input type="hidden" name="items[${index}][harga_setelah_diskon]" value="${item.harga}">
            `);
        });

        Swal.fire({
            title: 'Konfirmasi Transaksi',
            html: `
                <div style="text-align: left; padding: 10px;">
                    <p><strong>Pelanggan:</strong> ${$('#nama_pelanggan').val()}</p>
                    <p><strong>Total Belanja:</strong> Rp ${formatRupiah(totalBelanja.toString())}</p>
                    <p><strong>Jumlah Bayar:</strong> Rp ${formatRupiah(jumlahBayar.toString())}</p>
                    <p><strong>Kembalian:</strong> Rp ${formatRupiah((jumlahBayar - totalBelanja).toString())}</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-check-circle"></i> Ya, Proses!',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch('{{ route("sale.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        tanggal_transaksi: $('#tanggal_transaksi').val(),
                        nama_pelanggan: $('#nama_pelanggan').val(),
                        jumlah_bayar: jumlahBayar,
                        items: cart.map(item => ({
                            id: item.id,
                            nama: item.nama,
                            type: item.type,
                            harga_jual: item.diskon > 0 
                                ? Math.round(item.harga / (1 - item.diskon / 100))
                                : item.harga,
                            jumlah: item.jumlah,
                            diskon: item.diskon,
                            harga_setelah_diskon: item.harga
                        }))
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .catch(error => {
                    Swal.showValidationMessage(
                        `Request failed: ${error.message || 'Unknown error'}`
                    );
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                const response = result.value;
                
                Swal.fire({
                    icon: 'success',
                    title: 'Transaksi Berhasil!',
                    html: `
                        <div style="text-align: left; padding: 10px;">
                            <p><strong>ID Penjualan:</strong> ${response.data.id_penjualan}</p>
                            <p><strong>Total Belanja:</strong> Rp ${formatRupiah(response.data.total_belanja.toString())}</p>
                            <p><strong>Kembalian:</strong> Rp ${formatRupiah(response.data.kembalian.toString())}</p>
                        </div>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    // Reset form dan cart
                    cart = [];
                    updateCart();
                    $('#checkoutForm')[0].reset();
                    $('#tanggal_transaksi').val('{{ date("Y-m-d") }}');
                    $('#jumlah_bayar').val('');
                    
                    // Reload halaman untuk update stok
                    window.location.reload();
                });
            }
        });
    });
});
</script>

</body>
</html>