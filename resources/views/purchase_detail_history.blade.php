<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pembelian | Sistem Inventory dan Kasir</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    @include('layouts.navbar')
    @include('layouts.sidebar')
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="main-container">
        <div class="main-content">

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
                <div>
                    <h2 style="margin:0 0 8px 0;">Detail Transaksi Pembelian</h2>
                    <p style="margin:0;color:var(--text-secondary);font-size:14px;">
                        ID Pembelian: <strong style="color:var(--text-primary);">{{ $purchase->id_pembelian }}</strong>
                    </p>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button onclick="window.print()" class="btn-primary" style="display:inline-flex;align-items:center;gap:6px;padding:10px 18px;">
                        <i class="bi bi-printer"></i>
                        <span>Print Struk</span>
                    </button>
                </div>
            </div>

            <div style="background:linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);color:white;padding:24px;border-radius:12px;margin-bottom:24px;box-shadow:0 4px 12px rgba(14, 165, 233, 0.3);">
                <h3 style="margin:0 0 20px 0;font-size:18px;font-weight:700;color:white;">
                    <i class="bi bi-info-circle"></i> Informasi Transaksi
                </h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;">
                    <div style="background:rgba(255,255,255,0.15);padding:16px;border-radius:8px;backdrop-filter:blur(10px);">
                        <div style="font-size:13px;margin-bottom:8px;opacity:0.9;"><i class="bi bi-calendar3"></i> Tanggal Pembelian</div>
                        <div style="font-size:18px;font-weight:700;">{{ \Carbon\Carbon::parse($purchase->tgl_pembelian)->timezone('Asia/Jakarta')->format('d F Y, H:i') }}</div>
                    </div>
                    <div style="background:rgba(255,255,255,0.15);padding:16px;border-radius:8px;backdrop-filter:blur(10px);">
                        <div style="font-size:13px;margin-bottom:8px;opacity:0.9;"><i class="bi bi-truck"></i> Supplier</div>
                        <div style="font-size:18px;font-weight:700;">{{ $purchase->supplier->nama_supplier ?? '-' }}</div>
                    </div>
                    <div style="background:rgba(255,255,255,0.15);padding:16px;border-radius:8px;backdrop-filter:blur(10px);">
                        <div style="font-size:13px;margin-bottom:8px;opacity:0.9;"><i class="bi bi-box-seam"></i> Jumlah Item</div>
                        <div style="font-size:18px;font-weight:700;">{{ $purchase->purchaseDetails->count() }} produk</div>
                    </div>
                    <div style="background:rgba(255,255,255,0.15);padding:16px;border-radius:8px;backdrop-filter:blur(10px);">
                        <div style="font-size:13px;margin-bottom:8px;opacity:0.9;"><i class="bi bi-cash-stack"></i> Total Pembelian</div>
                        <div style="font-size:20px;font-weight:700;">Rp {{ number_format($purchase->total_pembelian, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <h3 style="margin:0 0 16px 0;font-size:18px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:8px;">
                <i class="bi bi-bag-check" style="color:var(--primary-color);"></i> Detail Produk
            </h3>

            <div class="table-responsive">
                <table class="table-main">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Produk</th>
                            <th>Nama Produk</th>
                            <th>Supplier</th>
                            <th>Stok Lama</th>
                            <th>Jumlah Beli</th>
                            <th>Stok Baru</th>
                            <th>Harga Beli</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchase->purchaseDetails as $index => $detail)
                        <tr>
                            <td data-label="No">{{ $index + 1 }}</td>
                            <td data-label="ID Produk">
                                <span class="id-badge">{{ $detail->id_produk }}</span>
                            </td>
                            <td data-label="Nama Produk">
                                <span class="product-name">{{ $detail->produk->nama_produk ?? 'Produk Tidak Ditemukan' }}</span>
                            </td>
                            <td data-label="Supplier">
                                <span class="product-name">{{ $detail->supplier->nama_supplier ?? '-' }}</span>
                            </td>
                            <td data-label="Stok Lama">
                                <span class="stock-number">{{ $detail->stok_lama }}</span>
                            </td>
                            <td data-label="Jumlah Beli">
                                <span class="badge badge-info">+{{ $detail->jumlah_beli }}</span>
                            </td>
                            <td data-label="Stok Baru">
                                <strong style="color:#10b981;font-weight:700;">{{ $detail->stok_baru }}</strong>
                            </td>
                            <td data-label="Harga Beli">Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                            <td data-label="Subtotal">
                                <strong>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="background:white;padding:24px;border-radius:12px;margin-top:24px;border:2px solid var(--gray-200);box-shadow:var(--shadow-sm);max-width:500px;margin-left:auto;">
                <h3 style="margin:0 0 20px 0;font-size:18px;font-weight:700;color:var(--text-primary);border-bottom:2px solid var(--primary-color);padding-bottom:10px;">
                    <i class="bi bi-calculator"></i> Ringkasan Pembelian
                </h3>
                <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--gray-200);">
                    <span style="font-size:15px;font-weight:600;color:var(--text-secondary);">Jumlah Item:</span>
                    <span style="font-size:18px;font-weight:700;color:var(--text-primary);">{{ $purchase->purchaseDetails->count() }} produk</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--gray-200);">
                    <span style="font-size:15px;font-weight:600;color:var(--text-secondary);">Total Unit Beli:</span>
                    <span style="font-size:18px;font-weight:700;color:var(--text-primary);">{{ $purchase->purchaseDetails->sum('jumlah_beli') }} unit</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:16px 0;border-top:2px solid var(--gray-300);margin-top:8px;">
                    <span style="font-size:16px;font-weight:700;color:var(--text-secondary);">Total Pembelian:</span>
                    <span style="font-size:22px;font-weight:700;color:var(--primary-color);">Rp {{ number_format($purchase->total_pembelian, 0, ',', '.') }}</span>
                </div>
            </div>

        </div>
    </div>

    <div class="nota-print">
        <div class="nota-kop">
            <div class="nota-toko-nama">DTC Multimedia</div>
            <div class="nota-toko-sub">Sistem Inventory &amp; Kasir</div>
            <div class="nota-dash"></div>
            <div class="nota-judul">*** NOTA PEMBELIAN ***</div>
        </div>

        <table class="nota-info-tbl">
            <tr>
                <td class="nik">No. Nota</td>
                <td class="sep">:</td>
                <td>#{{ $purchase->id_pembelian }}</td>
            </tr>
            <tr>
                <td class="nik">Tanggal</td>
                <td class="sep">:</td>
                <td>{{ \Carbon\Carbon::parse($purchase->tgl_pembelian)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="nik">Supplier</td>
                <td class="sep">:</td>
                <td>{{ $purchase->supplier->nama_supplier ?? '-' }}</td>
            </tr>
        </table>

        <div class="nota-dash"></div>

        <table class="nota-item-tbl">
            <thead>
                <tr>
                    <th class="col-nama">Produk</th>
                    <th class="col-qty">Qty</th>
                    <th class="col-harga">Harga</th>
                    <th class="col-total">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->purchaseDetails as $detail)
                <tr>
                    <td class="col-nama">{{ $detail->produk->nama_produk ?? 'Produk #' . $detail->id_produk }}</td>
                    <td class="col-qty">{{ $detail->jumlah_beli }}</td>
                    <td class="col-harga">{{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                    <td class="col-total">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="nota-dash"></div>

        <table class="nota-summary-tbl">
            <tr>
                <td class="skey">Total Item</td>
                <td class="ssep">:</td>
                <td class="sval">{{ $purchase->purchaseDetails->count() }} produk</td>
            </tr>
            <tr>
                <td class="skey">Total Unit</td>
                <td class="ssep">:</td>
                <td class="sval">{{ $purchase->purchaseDetails->sum('jumlah_beli') }} unit</td>
            </tr>
        </table>
        <div class="nota-dash nota-dash-sm"></div>
        <table class="nota-summary-tbl nota-total-row">
            <tr>
                <td class="skey">TOTAL</td>
                <td class="ssep">:</td>
                <td class="sval">Rp {{ number_format($purchase->total_pembelian, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="nota-dash"></div>

        <div class="nota-footer">
            <p>Dokumen sah sebagai bukti pembelian</p>
            <p>Barang yang sudah dibeli tidak</p>
            <p>dapat dikembalikan.</p>
            <p class="nota-footer-ts">{{ \Carbon\Carbon::parse($purchase->tgl_pembelian)->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }
    </script>

    <style>
        .nota-print { display: none; }

        @media print {
            body > *:not(.nota-print) { display: none !important; }
            .main-container,
            .navbar-container,
            .sidebar,
            .sidebar-overlay { display: none !important; }

            body {
                background: white !important;
                margin: 0; padding: 0;
            }

            .nota-print {
                display: block !important;
                width: 72mm;
                margin: 0 auto;
                padding: 4mm 2mm;
                font-family: 'Courier New', Courier, monospace;
                font-size: 10.5pt;
                color: #000;
                background: white;
            }

            .nota-kop { text-align: center; margin-bottom: 4px; }
            .nota-toko-nama {
                font-size: 15pt;
                font-weight: 900;
                text-transform: uppercase;
                letter-spacing: 1px;
                line-height: 1.3;
            }
            .nota-toko-sub {
                font-size: 9pt;
                margin-bottom: 2px;
            }
            .nota-judul {
                font-size: 10pt;
                font-weight: 700;
                letter-spacing: 1px;
                margin-top: 2px;
            }

            .nota-dash {
                border: none;
                border-top: 1px dashed #000;
                margin: 5px 0;
            }
            .nota-dash-sm { margin: 3px 0; }

            .nota-info-tbl {
                width: 100%;
                border-collapse: collapse;
                font-size: 10pt;
                margin: 3px 0;
            }
            .nota-info-tbl td { padding: 1px 0; vertical-align: top; }
            .nota-info-tbl .nik { width: 52px; }
            .nota-info-tbl .sep { width: 8px; text-align: center; }

            .nota-item-tbl {
                width: 100%;
                border-collapse: collapse;
                font-size: 10pt;
                margin: 3px 0;
            }
            .nota-item-tbl thead tr {
                border-top: 1px solid #000;
                border-bottom: 1px solid #000;
            }
            .nota-item-tbl th {
                padding: 3px 0;
                font-weight: 700;
            }
            .nota-item-tbl td {
                padding: 3px 0;
                vertical-align: top;
            }
            .nota-item-tbl tbody tr {
                border-bottom: 1px dotted #bbb;
            }
            .nota-item-tbl tbody tr:last-child {
                border-bottom: none;
            }

            .col-nama  { width: 44%; }
            .col-qty   { width: 10%; text-align: center; }
            .col-harga { width: 23%; text-align: right; }
            .col-total { width: 23%; text-align: right; }

            .nota-summary-tbl {
                width: 100%;
                border-collapse: collapse;
                font-size: 10.5pt;
                margin: 2px 0;
            }
            .nota-summary-tbl td { padding: 1.5px 0; }
            .nota-summary-tbl .skey { }
            .nota-summary-tbl .ssep { width: 10px; text-align: center; }
            .nota-summary-tbl .sval { text-align: right; font-weight: 600; }

            .nota-total-row {
                font-size: 13pt;
                font-weight: 900;
            }
            .nota-total-row .sval { font-weight: 900; }

            .nota-footer {
                text-align: center;
                font-size: 9.5pt;
                line-height: 1.7;
                margin-top: 4px;
            }
            .nota-footer-ts {
                font-size: 8pt;
                color: #555;
                margin-top: 4px;
            }
        }
    </style>
</body>
</html>