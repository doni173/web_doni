<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Service;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SaleController extends Controller
{
    /**
     * Menampilkan halaman transaksi penjualan
     */
    public function index(Request $request)
    {
        $q_barang = $request->query('q_barang');
        $q_service = $request->query('q_service');

        // Ambil data barang berdasarkan pencarian nama produk
        $items = Item::when($q_barang, function ($query) use ($q_barang) {
                return $query->where('nama_produk', 'like', "%{$q_barang}%");
            })
            ->orderBy('nama_produk', 'asc')
            ->get();

        // Ambil data layanan berdasarkan pencarian service
        $services = Service::when($q_service, function ($query) use ($q_service) {
                return $query->where('service', 'like', "%{$q_service}%");
            })
            ->orderBy('service', 'asc')
            ->get();

        return view('sale', compact('items', 'services'));
    }

    /**
     * Menampilkan halaman history penjualan
     */
    public function history(Request $request)
    {
        $q = $request->query('q');
        $tanggal = $request->query('tanggal');

        $sales = Sale::with(['customer', 'user'])
                    ->when($q, function ($query) use ($q) {
                        return $query->where(function($subQuery) use ($q) {
                            $subQuery->where('id_penjualan', 'like', "%{$q}%")
                                     ->orWhereHas('customer', function($customerQuery) use ($q) {
                                         $customerQuery->where('nama_pelanggan', 'like', "%{$q}%");
                                     })
                                     ->orWhereHas('user', function($userQuery) use ($q) {
                                         $userQuery->where('name', 'like', "%{$q}%");
                                     });
                        });
                    })
                    ->when($tanggal, function ($query) use ($tanggal) {
                        return $query->whereDate('tanggal_transaksi', $tanggal);
                    })
                    ->orderBy('tanggal_transaksi', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);

        return view('sale_history', compact('sales'));
    }

    /**
     * Menampilkan detail penjualan
     */
    public function show($id)
    {
        $sale = Sale::with(['customer', 'user', 'saleDetails.produk', 'saleDetails.service'])
                    ->findOrFail($id);

        return view('sale_detail_history', compact('sale'));
    }

    /**
     * Menyimpan transaksi penjualan baru
     */
    public function store(Request $request)
    {
        try {
            // Log request untuk debugging
            Log::info('Sale Store Request:', $request->all());

            // Validasi input - HAPUS tanggal_transaksi dari validasi
            $validated = $request->validate([
                'nama_pelanggan' => 'required|string|max:255',
                'jumlah_bayar' => 'required|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required',
                'items.*.nama' => 'required|string',
                'items.*.type' => 'required|string|in:produk,service',
                'items.*.harga_jual' => 'required|numeric|min:0',
                'items.*.jumlah' => 'required|integer|min:1',
                'items.*.diskon' => 'required|numeric|min:0|max:100',
                'items.*.harga_setelah_diskon' => 'required|numeric|min:0',
            ], [
                'items.required' => 'Keranjang belanja tidak boleh kosong!',
                'items.min' => 'Minimal harus ada 1 item dalam keranjang!',
                'items.*.id.required' => 'ID item tidak valid!',
                'items.*.nama.required' => 'Nama item tidak valid!',
                'items.*.type.required' => 'Tipe item tidak valid!',
                'items.*.type.in' => 'Tipe item harus produk atau service!',
                'items.*.harga_jual.required' => 'Harga jual tidak valid!',
                'items.*.jumlah.required' => 'Jumlah item tidak valid!',
                'items.*.jumlah.min' => 'Jumlah item minimal 1!',
                'items.*.diskon.required' => 'Diskon tidak valid!',
                'items.*.diskon.max' => 'Diskon maksimal 100%!',
                'items.*.harga_setelah_diskon.required' => 'Harga setelah diskon tidak valid!',
                'nama_pelanggan.required' => 'Nama pelanggan harus diisi!',
                'jumlah_bayar.required' => 'Jumlah bayar harus diisi!',
                'jumlah_bayar.min' => 'Jumlah bayar tidak boleh negatif!',
            ]);

            DB::beginTransaction();

            // ========================================
            // STEP 1: CEK STOK PRODUK
            // ========================================
            foreach ($validated['items'] as $item) {
                if ($item['type'] === 'produk') {
                    $produk = Item::find($item['id']);
                    
                    if (!$produk) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Produk "' . $item['nama'] . '" tidak ditemukan!'
                        ], 404);
                    }

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

            $kembalian = $validated['jumlah_bayar'] - $totalBelanja;
            
            if ($kembalian < 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah bayar kurang dari total belanja! Total: Rp ' . number_format($totalBelanja, 0, ',', '.') . ', Bayar: Rp ' . number_format($validated['jumlah_bayar'], 0, ',', '.')
                ], 400);
            }

            // ========================================
            // STEP 3: GENERATE ID PENJUALAN
            // ========================================
            $lastSale = Sale::orderBy('id_penjualan', 'desc')->first();
            $lastNumber = $lastSale ? intval($lastSale->id_penjualan) : 0;
            $idPenjualan = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

            // ========================================
            // STEP 4: BUAT/CEK CUSTOMER
            // ========================================
            $lastCustomer = Customer::orderBy('id_pelanggan', 'desc')->first();
            
            if ($lastCustomer) {
                $lastNumber = intval(substr($lastCustomer->id_pelanggan, 2));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            $idPelanggan = 'CS' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
            
            $customer = Customer::create([
                'id_pelanggan' => $idPelanggan,
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'no_hp' => '0',
            ]);

            // ========================================
            // STEP 5: SIMPAN KE TABEL SALES
            // ========================================
            // PENTING: SELALU gunakan waktu sekarang, ABAIKAN input dari form
            $tanggalTransaksi = Carbon::now('Asia/Jakarta');

            Log::info("Tanggal Transaksi yang akan disimpan: " . $tanggalTransaksi->toDateTimeString());

            $sale = Sale::create([
                'id_penjualan' => $idPenjualan,
                'id_user' => Auth::id() ?? 1,
                'id_pelanggan' => $customer->id_pelanggan,
                'total_belanja' => $totalBelanja,
                'total_bayar' => $totalBelanja,
                'jumlah_bayar' => $validated['jumlah_bayar'],
                'kembalian' => $kembalian,
                'tanggal_transaksi' => $tanggalTransaksi, // PERBAIKAN: Simpan dengan waktu lengkap
            ]);

            // ========================================
            // STEP 6: SIMPAN DETAIL & UPDATE STOK
            // ========================================
            foreach ($validated['items'] as $item) {
                $lastDetail = SaleDetail::orderBy('id_detail_penjualan', 'desc')->first();
                $lastDetailNumber = $lastDetail ? intval($lastDetail->id_detail_penjualan) : 0;
                $idDetail = str_pad($lastDetailNumber + 1, 5, '0', STR_PAD_LEFT);

                $idProduk = null;
                $idService = null;

                if ($item['type'] === 'produk') {
                    $idProduk = $item['id'];
                    
                    // UPDATE STOK PRODUK
                    $produk = Item::find($item['id']);
                    if ($produk) {
                        $stokLama = $produk->stok;
                        $produk->stok -= $item['jumlah'];
                        $produk->save();
                        
                        Log::info("Stok Update: {$produk->nama_produk} - Stok Lama: {$stokLama}, Terjual: {$item['jumlah']}, Stok Baru: {$produk->stok}");
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

            Log::info("Transaksi Berhasil: ID {$idPenjualan}, Total: {$totalBelanja}, Kembalian: {$kembalian}, Waktu: {$tanggalTransaksi->toDateTimeString()}");

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan!',
                'data' => [
                    'id_penjualan' => $idPenjualan,
                    'total_belanja' => $totalBelanja,
                    'kembalian' => $kembalian,
                    'nama_pelanggan' => $validated['nama_pelanggan'],
                    'tanggal_transaksi' => $tanggalTransaksi->format('d F Y H:i:s'),
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Validation Error:', ['errors' => $e->errors()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid!',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction Error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus transaksi penjualan
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            // Cari transaksi
            $sale = Sale::findOrFail($id);
            
            // Kembalikan stok barang yang terjual
            $saleDetails = SaleDetail::where('id_penjualan', $id)->get();
            
            foreach ($saleDetails as $detail) {
                // Hanya kembalikan stok untuk produk, bukan service
                if ($detail->id_produk) {
                    $item = Item::find($detail->id_produk);
                    if ($item) {
                        // Kembalikan stok
                        $stokLama = $item->stok;
                        $item->stok += $detail->jumlah;
                        $item->save();
                        
                        Log::info("Stok dikembalikan: {$item->nama_produk} - Stok Lama: {$stokLama}, Dikembalikan: {$detail->jumlah}, Stok Baru: {$item->stok}");
                    }
                }
            }
            
            // Hapus detail penjualan
            SaleDetail::where('id_penjualan', $id)->delete();
            
            // Hapus customer jika hanya punya 1 transaksi
            $customer = Customer::find($sale->id_pelanggan);
            if ($customer) {
                $customerSalesCount = Sale::where('id_pelanggan', $customer->id_pelanggan)->count();
                if ($customerSalesCount <= 1) {
                    $customer->delete();
                    Log::info("Customer dihapus: {$customer->nama_pelanggan}");
                }
            }
            
            // Hapus transaksi penjualan
            $sale->delete();
            
            DB::commit();
            
            Log::info("Transaksi berhasil dihapus: ID {$id}");
            
            return redirect()->route('sale.history')->with('success', 'Transaksi berhasil dihapus dan stok dikembalikan!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting sale: ' . $e->getMessage());
            
            return redirect()->route('sale.history')->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Cetak struk penjualan
     */
    public function print($id)
    {
        try {
            $sale = Sale::with(['customer', 'user', 'saleDetails.produk', 'saleDetails.service'])
                        ->findOrFail($id);
            
            return view('sale_print', compact('sale'));
            
        } catch (\Exception $e) {
            Log::error('Error printing sale: ' . $e->getMessage());
            return redirect()->route('sale.history')->with('error', 'Gagal mencetak struk: ' . $e->getMessage());
        }
    }
}