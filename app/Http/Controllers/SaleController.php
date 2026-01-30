<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Service;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $q_barang = $request->query('q_barang');  // Pencarian barang
        $q_service = $request->query('q_service');  // Pencarian service

        // Ambil data barang berdasarkan pencarian nama produk
        $items = Item::when($q_barang, function ($query) use ($q_barang) {
                return $query->where('nama_produk', 'like', "%{$q_barang}%");
            })
            ->get();

        // Ambil data layanan berdasarkan pencarian service
        $services = Service::when($q_service, function ($query) use ($q_service) {
                return $query->where('service', 'like', "%{$q_service}%");
            })
            ->get();

        // Mengirimkan data ke view
        return view('sale', compact('items', 'services'));
    }

    /**
 * Menampilkan halaman history penjualan
 */
public function history(Request $request)
{
    // Ambil parameter tanggal dari request
    $tanggal = $request->query('tanggal');

    // Query untuk mengambil data penjualan dengan filter tanggal
    $sales = Sale::with('customer')
                ->when($tanggal, function ($query) use ($tanggal) {
                    return $query->whereDate('tanggal_transaksi', $tanggal);
                })
                ->orderBy('tanggal_transaksi', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

    return view('history', compact('sales'));
}

public function report(Request $request)
{
    // Ambil parameter report_type dari request
    $reportType = $request->query('report_type', 'daily'); // Default ke harian

    // Query untuk mengambil data berdasarkan periode yang dipilih
    $sales = Sale::query();

    switch ($reportType) {
        case 'daily':
            $sales->whereDate('tanggal_transaksi', Carbon::today());
            break;
        case 'weekly':
            $sales->whereBetween('tanggal_transaksi', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ]);
            break;
        case 'monthly':
            $sales->whereMonth('tanggal_transaksi', Carbon::now()->month);
            break;
        case 'yearly':
            $sales->whereYear('tanggal_transaksi', Carbon::now()->year);
            break;
        default:
            $sales->whereDate('tanggal_transaksi', Carbon::today());
            break;
    }

    // Ambil data penjualan
    $sales = $sales->with('customer', 'user') // Mengambil relasi customer dan user (kasir)
                   ->orderBy('tanggal_transaksi', 'desc')
                   ->get();

    // Menghitung total penjualan dan keuntungan
    $totalPenjualan = $sales->sum('total_belanja');
    $totalKeuntungan = $sales->sum(function($sale) {
        return $sale->jumlah_bayar - $sale->total_belanja;
    });

    // Kirim data ke view laporan
    return view('report', compact('sales', 'reportType', 'totalPenjualan', 'totalKeuntungan'));
}

/**
 * Menampilkan detail penjualan
 */
public function show($id)
{
    $sale = Sale::with(['customer', 'saleDetails.produk', 'saleDetails.service'])
                ->findOrFail($id);

    return view('history_detail', compact('sale'));
}

    // Menyelesaikan penjualan (menerima data dari localStorage)
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'tanggal_transaksi' => 'required|date',
            'nama_pelanggan' => 'required|string|max:255',
            'jumlah_bayar' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|string',
            'items.*.nama' => 'required|string',
            'items.*.type' => 'required|string|in:produk,service',
            'items.*.harga_jual' => 'required|numeric',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.diskon' => 'required|numeric|min:0|max:100',
            'items.*.harga_setelah_diskon' => 'required|numeric|min:0',
        ]);

        try {
            // Mulai database transaction
            DB::beginTransaction();

            // ========================================
            // STEP 1: CEK STOK PRODUK TERLEBIH DAHULU
            // ========================================
            foreach ($validated['items'] as $item) {
                // Hanya cek stok untuk PRODUK, bukan SERVICE
                if ($item['type'] === 'produk') {
                    $produk = Item::find($item['id']);
                    
                    // Cek apakah produk ditemukan
                    if (!$produk) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Produk "' . $item['nama'] . '" tidak ditemukan di database!'
                        ], 404);
                    }

                    // Cek apakah stok mencukupi
                    if ($produk->stok < $item['jumlah']) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Stok produk "' . $item['nama'] . '" tidak mencukupi! Stok tersedia: ' . $produk->stok . ', diminta: ' . $item['jumlah']
                        ], 400);
                    }
                }
            }

            // ========================================
            // STEP 2: HITUNG TOTAL BELANJA & KEMBALIAN
            // ========================================
            $totalBelanja = 0;
            foreach ($validated['items'] as $item) {
                $totalBelanja += $item['harga_setelah_diskon'] * $item['jumlah'];
            }

            // Hitung kembalian
            $kembalian = $validated['jumlah_bayar'] - $totalBelanja;
            
            // Pastikan kembalian tidak negatif
            if ($kembalian < 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah bayar kurang dari total belanja!'
                ], 400);
            }

            // ========================================
            // STEP 3: GENERATE ID PENJUALAN
            // ========================================
            $lastSale = Sale::orderBy('id_penjualan', 'desc')->first();
            $lastNumber = $lastSale ? intval($lastSale->id_penjualan) : 0;
            $idPenjualan = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

            // ========================================
            // STEP 4: BUAT CUSTOMER BARU
            // ========================================
            $lastCustomer = \App\Models\Customer::orderBy('id_pelanggan', 'desc')->first();
            
            if ($lastCustomer) {
                $lastNumber = intval(substr($lastCustomer->id_pelanggan, 2));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            $idPelanggan = 'CS' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
            
            $customer = \App\Models\Customer::create([
                'id_pelanggan' => $idPelanggan,
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'no_hp' => 0,
            ]);

            // ========================================
            // STEP 5: SIMPAN KE TABEL SALES
            // ========================================
            $sale = Sale::create([
                'id_penjualan' => $idPenjualan,
                'id_user' => Auth::id() ?? 'USR01',
                'id_pelanggan' => $customer->id_pelanggan,
                'total_belanja' => $totalBelanja,
                'total_bayar' => $totalBelanja,
                'jumlah_bayar' => $validated['jumlah_bayar'],
                'kembalian' => $kembalian,
                'tanggal_transaksi' => $validated['tanggal_transaksi'],
            ]);

            // ========================================
            // STEP 6: SIMPAN DETAIL & UPDATE STOK
            // ========================================
            foreach ($validated['items'] as $item) {
                // Generate ID Detail unik
                $lastDetail = SaleDetail::orderBy('id_detail_penjualan', 'desc')->first();
                $lastDetailNumber = $lastDetail ? intval($lastDetail->id_detail_penjualan) : 0;
                $idDetail = str_pad($lastDetailNumber + 1, 5, '0', STR_PAD_LEFT);

                // Tentukan ID produk atau service
                $idProduk = null;
                $idService = null;

                if ($item['type'] === 'produk') {
                    $idProduk = $item['id'];
                    
                    // UPDATE STOK PRODUK - KURANGI SESUAI JUMLAH TERJUAL
                    $produk = Item::find($item['id']);
                    if ($produk) {
                        $produk->stok -= $item['jumlah'];
                        $produk->save();
                        
                        // Log untuk tracking (opsional)
                        \Log::info("Stok produk {$produk->nama_produk} dikurangi {$item['jumlah']}. Stok tersisa: {$produk->stok}");
                    }
                    
                } else {
                    $idService = $item['id'];
                }

                // Simpan detail penjualan
                SaleDetail::create([
                    'id_detail_penjualan' => $idDetail,
                    'id_penjualan' => $idPenjualan,
                    'id_produk' => $idProduk,
                    'id_service' => $idService,
                    'jumlah' => $item['jumlah'],
                    'diskon' => $item['diskon'],
                    'harga_setelah_diskon' => $item['harga_setelah_diskon'],
                ]);
            }

            // ========================================
            // STEP 7: COMMIT TRANSACTION
            // ========================================
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan dan stok telah diperbarui!',
                'data' => [
                    'id_penjualan' => $idPenjualan,
                    'total_belanja' => $totalBelanja,
                    'kembalian' => $kembalian,
                ]
            ], 201);

        } catch (\Exception $e) {
            // Rollback jika terjadi error
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}