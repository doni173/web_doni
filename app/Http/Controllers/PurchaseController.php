<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseDetails;
use Exception;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     * Menampilkan halaman pembelian dengan daftar produk
     */
    public function index(Request $request)
    {
        try {
            // Query untuk search produk
            $query = Item::with(['kategori', 'brand']);
            
            // Search functionality
            if ($request->has('q_barang') && $request->q_barang != '') {
                $searchTerm = $request->q_barang;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nama_produk', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('kategori', function($q) use ($searchTerm) {
                          $q->where('kategori', 'like', '%' . $searchTerm . '%');
                      })
                      ->orWhereHas('brand', function($q) use ($searchTerm) {
                          $q->where('brand', 'like', '%' . $searchTerm . '%');
                      });
                });
            }
            
            // Ambil semua produk
            $items = $query->orderBy('nama_produk', 'asc')->get();
            
            return view('purchase.index', compact('items'));
            
        } catch (Exception $e) {
            Log::error('Error loading purchase page: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat halaman pembelian');
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan transaksi pembelian baru
     */
    public function store(Request $request)
    {
        try {
            // Validasi request
            $validated = $request->validate([
                'tanggal_pembelian' => 'required|date',
                'nama_supplier' => 'required|string|max:255',
                'nomor_invoice' => 'required|string|max:100',
                'total_pembelian' => 'required|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required',
                'items.*.type' => 'required|string',
                'items.*.nama' => 'required|string',
                'items.*.jumlah' => 'required|numeric|min:1',
                'items.*.harga_beli' => 'required|numeric|min:0',
                'items.*.total' => 'required|numeric|min:0',
            ]);

            // Log untuk debugging
            Log::info('Purchase Request Data:', $validated);

            DB::beginTransaction();

            try {
                // 1. Simpan data pembelian utama
                $pembelian = Pembelian::create([
                    'tanggal_pembelian' => $validated['tanggal_pembelian'],
                    'nama_supplier' => $validated['nama_supplier'],
                    'nomor_invoice' => $validated['nomor_invoice'],
                    'total_pembelian' => $validated['total_pembelian'],
                    'status' => 'completed',
                    'created_by' => auth()->id() ?? 1, // ID user yang login
                ]);

                Log::info('Pembelian created:', ['id' => $pembelian->id_pembelian]);

                // 2. Simpan detail pembelian dan update stok
                foreach ($validated['items'] as $item) {
                    // Simpan detail pembelian
                    $detail = PembelianDetail::create([
                        'id_pembelian' => $pembelian->id_pembelian,
                        'id_produk' => $item['id'],
                        'jumlah' => $item['jumlah'],
                        'harga_beli' => $item['harga_beli'],
                        'subtotal' => $item['total'],
                    ]);

                    Log::info('Purchase detail created:', [
                        'detail_id' => $detail->id,
                        'produk_id' => $item['id']
                    ]);

                    // 3. Update stok produk
                    $produk = Produk::findOrFail($item['id']);
                    $stokLama = $produk->stok;
                    $stokBaru = $stokLama + $item['jumlah'];
                    
                    $produk->update([
                        'stok' => $stokBaru,
                        'harga_beli' => $item['harga_beli'], // Update harga beli terbaru
                    ]);

                    Log::info('Stock updated:', [
                        'produk' => $produk->nama_produk,
                        'stok_lama' => $stokLama,
                        'jumlah_beli' => $item['jumlah'],
                        'stok_baru' => $stokBaru
                    ]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Pembelian berhasil disimpan',
                    'data' => [
                        'id_pembelian' => $pembelian->id_pembelian,
                        'nomor_invoice' => $pembelian->nomor_invoice,
                        'total_pembelian' => $pembelian->total_pembelian,
                    ]
                ], 200);

            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Database transaction error: ' . $e->getMessage());
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Purchase store error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pembelian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail pembelian
     */
    public function show($id)
    {
        try {
            $pembelian = Pembelian::with(['details.produk'])
                ->findOrFail($id);
            
            return view('purchase.show', compact('pembelian'));
            
        } catch (Exception $e) {
            Log::error('Error loading purchase detail: ' . $e->getMessage());
            return back()->with('error', 'Data pembelian tidak ditemukan');
        }
    }

    /**
     * Show the history of purchases
     * Menampilkan riwayat pembelian
     */
    public function history(Request $request)
    {
        try {
            $query = Pembelian::with(['details.produk']);
            
            // Filter by date range
            if ($request->has('start_date') && $request->start_date != '') {
                $query->whereDate('tanggal_pembelian', '>=', $request->start_date);
            }
            
            if ($request->has('end_date') && $request->end_date != '') {
                $query->whereDate('tanggal_pembelian', '<=', $request->end_date);
            }
            
            // Filter by supplier
            if ($request->has('supplier') && $request->supplier != '') {
                $query->where('nama_supplier', 'like', '%' . $request->supplier . '%');
            }
            
            // Filter by invoice
            if ($request->has('invoice') && $request->invoice != '') {
                $query->where('nomor_invoice', 'like', '%' . $request->invoice . '%');
            }
            
            $purchases = $query->orderBy('tanggal_pembelian', 'desc')
                ->paginate(20);
            
            return view('purchase.history', compact('purchases'));
            
        } catch (Exception $e) {
            Log::error('Error loading purchase history: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat riwayat pembelian');
        }
    }

    /**
     * Delete purchase transaction
     * Menghapus transaksi pembelian (opsional, hati-hati dengan stok)
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $pembelian = Pembelian::with('details')->findOrFail($id);
            
            // Kembalikan stok sebelum menghapus
            foreach ($pembelian->details as $detail) {
                $produk = Produk::findOrFail($detail->id_produk);
                $produk->update([
                    'stok' => $produk->stok - $detail->jumlah
                ]);
            }
            
            // Hapus detail dan pembelian
            PembelianDetail::where('id_pembelian', $id)->delete();
            $pembelian->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil dihapus'
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting purchase: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pembelian'
            ], 500);
        }
    }

    /**
     * Generate purchase report
     * Menghasilkan laporan pembelian
     */
    public function report(Request $request)
    {
        try {
            $startDate = $request->get('start_date', date('Y-m-01'));
            $endDate = $request->get('end_date', date('Y-m-d'));
            
            $purchases = Pembelian::with(['details.produk'])
                ->whereBetween('tanggal_pembelian', [$startDate, $endDate])
                ->orderBy('tanggal_pembelian', 'desc')
                ->get();
            
            $summary = [
                'total_transaksi' => $purchases->count(),
                'total_pembelian' => $purchases->sum('total_pembelian'),
                'total_item' => $purchases->sum(function($p) {
                    return $p->details->sum('jumlah');
                }),
                'suppliers' => $purchases->pluck('nama_supplier')->unique()->count(),
            ];
            
            return view('purchase.report', compact('purchases', 'summary', 'startDate', 'endDate'));
            
        } catch (Exception $e) {
            Log::error('Error generating purchase report: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat laporan');
        }
    }
}