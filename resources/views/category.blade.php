<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori | Sistem Inventory dan Kasir</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

@include('layouts.sidebar')
@include('layouts.navbar')

<div class="container">
    <div class="main-content">
        <h2>Data Kategori</h2>
        <div class="buttons">
            <!-- Form Pencarian -->
            <form action="{{ route('categories.index') }}" method="GET">
                <input type="text" class="form-control" placeholder="Cari kategori..." name="q" value="{{ request('q') }}" style="width: 300px;">
                <button type="submit" class="btn-src">Search</button>
                <button type="button" class="btn-add" data-toggle="modal" data-target="#addCategoryModal">Tambah Data </button>
            </form>
        </div><br>

        <table class="table-main">
            <thead>
                <tr>
                    <th>Id Kategori</th>
                    <th>Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                <tr>
                    <td>{{ $category->id_kategori }}</td>
                    <td>{{ $category->kategori }}</td>
                    <td>
                        <!-- Tombol Edit -->
                        <button type="button" class="btn-edit" data-toggle="modal" data-target="#editCategoryModal{{ $category->id_kategori }}">
                            <i class="fas fa-edit"></i> 
                        </button>

                        <!-- Tombol Hapus -->
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $category->id_kategori }}')">
                            <i class="fas fa-trash-alt"></i> 
                        </button>

                        <!-- Form Penghapusan -->
                        <form id="delete-form-{{ $category->id_kategori }}" action="{{ route('categories.destroy', $category->id_kategori) }}" method="POST" style="display: none;">
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

<!-- Modal untuk Tambah Kategori -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Tambah Kategori</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form untuk menambah kategori -->
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="Kategori">Kategori</label>
                        <input type="text" class="form-control" id="kategori" name="kategori" required>
                    </div>
                    <button type="submit" class="btn btn-success">Tambah</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Edit Kategori -->
@foreach($categories as $category)
<div class="modal fade" id="editCategoryModal{{ $category->id_kategori }}" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel{{ $category->id_kategori }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="editCategoryModalLabel{{ $category->id_kategori }}">Edit Kategori</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form untuk mengedit kategori -->
                <form action="{{ route('categories.update', $category->id_kategori) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="Kategori">Kategori</label>
                        <input type="text" class="form-control" id="kategori" name="kategori" value="{{ $category->kategori }}" required>
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
