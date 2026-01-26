<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang | Sistem Inventory dan Kasir</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

@include('layouts.sidebar')
@include('layouts.navbar')

<div class="container">
    <div class="main-content">
        <h2>Data Barang</h2>
        <div class="buttons">
            <form action="{{ route('items.index') }}" method="GET">
                <input type="text" class="form-control" placeholder="Cari Barang..." name="q" value="{{ request('q') }}" style="width: 300px;">
                <button type="submit" class="btn-src">Search</button>
                <button type="button" class="btn-add" data-toggle="modal" data-target="#addItemModal">Tambah Data</button>
            </form>
        </div><br>

        <table class="table-main">
            <thead>
                <tr>
                    <th>Id Produk</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Brand</th>
                    <th>Stok</th>
                    <th>Satuan</th>
                    <th>FSN</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Diskon</th>
                    <th>Harga Diskon</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->id_produk }}</td>
                    <td>{{ $item->nama_produk }}</td>
                    <td>{{ $item->kategori->kategori }}</td>
                    <td>{{ $item->brand->brand }}</td>
                    <td>{{ $item->stok }}</td> 
                    <td>{{ $item->satuan }}</td>
                    <td>{{ $item->FSN }}</td>
                    <td>Rp.{{ number_format($item->modal) }}</td>
                    <td>Rp.{{ number_format($item->harga_jual) }}</td>
                    <td>{{ $item->diskon }}%</td>
                    <td>Rp.{{ number_format($item->harga_setelah_diskon) }}</td>
                     <!-- Menampilkan harga setelah diskon -->
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#editItemModal{{ $item->id_produk }}">Edit</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $item->id_produk }}')">Hapus</button>
                        <form id="delete-form-{{ $item->id_produk }}" action="{{ route('items.destroy', $item->id_produk) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>

<!-- Modal untuk Tambah Item -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Tambah Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('items.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="nama_produk">Nama Produk :</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" required>
                    </div>
                    <div class="form-group">
                        <label for="id_kategori">Kategori______:</label>
                        <select class="form-control" id="id_kategori" name="id_kategori" required>
                            @foreach($categories as $category)
                            <option value="{{ $category->id_kategori }}">{{ $category->kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_brand">Brand</label>
                        <select class="form-control" id="id_brand" name="id_brand" required>
                            @foreach($brands as $brand)
                            <option value="{{ $brand->id_brand }}">{{ $brand->brand }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="harga_jual">Harga Jual</label>
                        <input type="number" class="form-control" id="harga_jual" name="harga_jual" required>
                    </div>
                    <div class="form-group">
                        <label for="stok">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" required>
                    </div>
                    <div class="form-group">
                        <label for="satuan">Satuan</label>
                        <input type="text" class="form-control" id="satuan" name="satuan" required>
                    </div>
                    <div class="form-group">
                        <label for="modal">Modal</label>
                        <input type="number" class="form-control" id="modal" name="modal" required>
                    </div>
                    <div class="form-group">
                        <label for="FSN">FSN</label>
                        <select class="form-control" id="FSN" name="FSN" required>
                            <option value="F">F</option>
                            <option value="S">S</option>
                            <option value="N">N</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="diskon">Diskon</label>
                        <input type="number" class="form-control" id="diskon" name="diskon" required>
                    </div>
                    <button type="submit" class="btn btn-success">Tambah</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Edit Item -->
@foreach($items as $item)
<div class="modal fade" id="editItemModal{{ $item->id_produk }}" tabindex="-1" role="dialog" aria-labelledby="editItemModalLabel{{ $item->id_produk }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editItemModalLabel{{ $item->id_produk }}">Edit Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('items.update', $item->id_produk) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="nama_produk">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="{{ $item->nama_produk }}" required>
                    </div>
                    <div class="form-group">
                        <label for="id_kategori">Kategori</label>
                        <select class="form-control" id="id_kategori" name="id_kategori" required>
                            @foreach($categories as $category)
                            <option value="{{ $category->id_kategori }}" {{ $category->id_kategori == $item->id_kategori ? 'selected' : '' }}>
                                {{ $category->kategori }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_brand">Brand</label>
                        <select class="form-control" id="id_brand" name="id_brand" required>
                            @foreach($brands as $brand)
                            <option value="{{ $brand->id_brand }}" {{ $brand->id_brand == $item->id_brand ? 'selected' : '' }}>
                                {{ $brand->brand }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="harga_jual">Harga Jual</label>
                        <input type="number" class="form-control" id="harga_jual" name="harga_jual" value="{{ $item->harga_jual }}" required>
                    </div>
                    <div class="form-group">
                        <label for="stok">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" value="{{ $item->stok }}" required>
                    </div>
                    <div class="form-group">
                        <label for="satuan">Satuan</label>
                        <input type="text" class="form-control" id="satuan" name="satuan" value="{{ $item->satuan }}" required>
                    </div>
                    <div class="form-group">
                        <label for="modal">Modal</label>
                        <input type="number" class="form-control" id="modal" name="modal" value="{{ $item->modal }}" required>
                    </div>
                    <div class="form-group">
                        <label for="FSN">FSN</label>
                        <select class="form-control" id="FSN" name="FSN" required>
                            <option value="F" {{ $item->FSN == 'F' ? 'selected' : '' }}>F</option>
                            <option value="S" {{ $item->FSN == 'S' ? 'selected' : '' }}>S</option>
                            <option value="N" {{ $item->FSN == 'N' ? 'selected' : '' }}>N</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="diskon">Diskon</label>
                        <input type="number" class="form-control" id="diskon" name="diskon" value="{{ $item->diskon }}" required>
                    </div>
                    <button type="submit" class="btn btn-success">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- SweetAlert for success -->
@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '{{ session('success') }}',
        timer: 2000,
        showConfirmButton: false
    })
</script>
@endif

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data ini akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit(); // Mengirim form penghapusan
            }
        });
    }
</script>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
