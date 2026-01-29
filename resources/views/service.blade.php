<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service | Sistem Inventory dan Kasir</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

@include('layouts.sidebar')
@include('layouts.navbar')

<div class="container">
    <div class="main-content">
        <h2>Data Service</h2>
        <div class="buttons">
            <!-- Form Pencarian -->
            <form action="{{ route('services.index') }}" method="GET">
                <input type="text" class="form-control" placeholder="Cari service..." name="q" value="{{ request('q') }}" style="width: 300px;">
                <button type="submit" class="btn-src">Search</button>
                <button type="button" class="btn-add" data-toggle="modal" data-target="#addServiceModal">Tambah Data</button>
            </form>
        </div><br>
        <table class="table-main">
            <thead>
                <tr>
                    <th>Id Service</th>
                    <th>Service</th>
                    <th>Harga Jual</th>
                    <th>Diskon</th>
                    <th>Harga Diskon</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $service)
                <tr>
                    <td>{{ $service->id_service }}</td>
                    <td>{{ $service->service }}</td>
                    <td>{{ number_format($service->harga_jual) }}</td>
                    <td>{{ $service->diskon }}%</td>
                    <td>{{ number_format($service->harga_setelah_diskon) }}</td>
                    <td>
                        <!-- Tombol Edit -->
                        <button type="button" class="btn-edit" data-toggle="modal" data-target="#editServiceModal{{ $service->id_service }}">
                            <i class="fas fa-edit"></i> 
                        </button>

                        <!-- Tombol Hapus -->
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $service->id_service }}')">
                            <i class="fas fa-trash-alt"></i>
                        </button>

                        <!-- Form Penghapusan -->
                        <form id="delete-form-{{ $service->id_service }}" action="{{ route('services.destroy', $service->id_service) }}" method="POST" style="display: none;">
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

<!-- Modal untuk Tambah Service -->
<div class="modal fade" id="addServiceModal" tabindex="-1" role="dialog" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addServiceModalLabel">Tambah Service</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form untuk menambah service -->
                <form action="{{ route('services.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="service">Service</label>
                        <input type="text" class="form-control" id="service" name="service" required>
                    </div>

                    <div class="form-group">
                        <label for="harga_ jual">Harga Jual</label>
                        <input type="number" class="form-control" id="harga_jual" name="harga_jual" required>
                    </div>

                    <div class="form-group">
                        <label for="diskon">Diskon</label>
                        <input type="text" class="form-control" id="diskon" name="diskon" required>
                    </div>

                    <button type="submit" class="btn btn-success">Tambah</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Edit Service -->
@foreach($services as $service)
<div class="modal fade" id="editServiceModal{{ $service->id_service }}" tabindex="-1" role="dialog" aria-labelledby="editServiceModalLabel{{ $service->id_service }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editServiceModalLabel{{ $service->id_service }}">Edit Service</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form untuk mengedit service -->
                <form action="{{ route('services.update', $service->id_service) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="service">Service</label>
                        <input type="text" class="form-control" id="service" name="service" value="{{ $service->service }}" required>
                    </div>

                    <div class="form-group">
                        <label for="harga">Harga_jual</label>
                        <input type="number" class="form-control" id="harga_jual" name="harga_jual" value="{{ $service->harga_jual }}" required>
                    </div>

                    <div class="form-group">
                        <label for="harga">Diskon</label>
                        <input type="number" class="form-control" id="diskon" name="diskon" value="{{ $service->diskon }}" required>
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
