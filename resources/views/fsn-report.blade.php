<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan FSN Analysis</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

@include('layouts.sidebar')
@include('layouts.navbar')

<div class="container">
    <div class="main-content">
        <h2>Laporan FSN Analysis</h2>
        
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Fast Moving (F)</h5>
                        <h2>{{ $summary['fast_moving'] }}</h2>
                        <small>TOR > 3</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5>Slow Moving (S)</h5>
                        <h2>{{ $summary['slow_moving'] }}</h2>
                        <small>1 ≤ TOR ≤ 3</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5>Non Moving (N)</h5>
                        <h2>{{ $summary['non_moving'] }}</h2>
                        <small>TOR < 1</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <h5>Belum Dianalisis</h5>
                        <h2>{{ $summary['not_analyzed'] }}</h2>
                        <small>FSN = NA</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Detail -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID Produk</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Brand</th>
                    <th>Stok</th>
                    <th>TOR Value</th>
                    <th>FSN</th>
                    <th>Terakhir Dihitung</th>
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
                    <td>{{ number_format($item->tor_value, 2) }}</td>
                    <td>
                        <span class="badge 
                            @if($item->FSN == 'F') badge-success
                            @elseif($item->FSN == 'S') badge-warning
                            @elseif($item->FSN == 'N') badge-danger
                            @else badge-secondary
                            @endif">
                            {{ $item->FSN }}
                        </span>
                    </td>
                    <td>
                        {{ $item->last_fsn_calculation ? $item->last_fsn_calculation->format('d/m/Y H:i') : '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>