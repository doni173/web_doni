<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Item;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Services\FSNCalculationService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    protected $fsnService;

    const MIN_OBSERVATION_DAYS = 30;
    const DEFAULT_FSN_PERIOD   = 90;

    public function __construct(FSNCalculationService $fsnService)
    {
        $this->fsnService = $fsnService;
    }

    // ============================================================
    // INDEX
    // ============================================================
    public function index(Request $request)
    {
        $searchQuery = $request->query('q');

        $items = Item::with(['kategori', 'brand', 'supplier'])
            ->when($searchQuery, function ($query) use ($searchQuery) {
                return $query->where('nama_produk', 'like', "%{$searchQuery}%")
                    ->orWhere('id_produk', 'like', "%{$searchQuery}%");
            })
            ->orderBy('id_produk', 'asc')
            ->get();

        $categories = Category::orderBy('kategori')->get();
        $brands     = Brand::orderBy('brand')->get();
        $suppliers  = Supplier::orderBy('nama_supplier')->get();

        return view('item', compact(
            'items',
            'searchQuery',
            'categories',
            'brands',
            'suppliers'
        ));
    }

    // ============================================================
    // STORE
    // ============================================================
    public function store(Request $request)
    {
        try {
            // ✅ Bersihkan format rupiah sebelum validasi
            $request->merge([
                'harga_jual' => str_replace('.', '', $request->harga_jual),
                'modal'      => str_replace('.', '', $request->modal),
            ]);

            $validated = $request->validate([
                'nama_produk'   => 'required|string|max:100',
                'tanggal_masuk' => 'nullable|date|before_or_equal:today',
                'harga_jual'    => 'required|numeric|min:0',
                'id_kategori'   => 'required|exists:categories,id_kategori',
                'id_brand'      => 'required|exists:brands,id_brand',
                'id_supplier'   => 'required|exists:suppliers,id_supplier',
                'stok'          => 'required|numeric|min:0',
                'satuan'        => 'required|string|max:30',
                'modal'         => 'required|numeric|min:0',
            ]);

            $idProduk           = $this->generateProductId();
            $hargaSetelahDiskon = $validated['harga_jual']; // Diskon awal = 0

            Item::create([
                'id_produk'            => $idProduk,
                'tanggal_masuk'        => $validated['tanggal_masuk'] ?? now(),
                'nama_produk'          => $validated['nama_produk'],
                'id_kategori'          => $validated['id_kategori'],
                'id_brand'             => $validated['id_brand'],
                'id_supplier'          => $validated['id_supplier'],
                'harga_jual'           => $validated['harga_jual'],
                'stok'                 => $validated['stok'],
                'satuan'               => $validated['satuan'],
                'modal'                => $validated['modal'],
                'FSN'                  => 'NA',
                'tor_value'            => null,
                'diskon'               => 0,
                'harga_setelah_diskon' => $hargaSetelahDiskon,
                'consecutive_n_months' => 0,
            ]);

            Log::info('Item Created', [
                'id_produk'   => $idProduk,
                'nama_produk' => $validated['nama_produk'],
                'harga_jual'  => $validated['harga_jual'],
            ]);

            return redirect()
                ->route('items.index')
                ->with('success', 'Produk berhasil ditambahkan. FSN akan dihitung setelah periode observasi ' . self::MIN_OBSERVATION_DAYS . ' hari.');

        } catch (\Exception $e) {
            Log::error('Item Creation Error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    // ============================================================
    // UPDATE
    // ============================================================
    public function update(Request $request, $id)
    {
        try {
            // ✅ Bersihkan format rupiah sebelum validasi
            // Ini fix utama — hapus titik dari format 170.000 → 170000
            $request->merge([
                'harga_jual' => str_replace('.', '', $request->harga_jual),
                'modal'      => str_replace('.', '', $request->modal),
            ]);

            $validated = $request->validate([
                'nama_produk'   => 'required|string|max:100',
                'tanggal_masuk' => 'required|date|before_or_equal:today',
                'harga_jual'    => 'required|numeric|min:0',
                'id_kategori'   => 'required|exists:categories,id_kategori',
                'id_brand'      => 'required|exists:brands,id_brand',
                'id_supplier'   => 'required|exists:suppliers,id_supplier',
                'stok'          => 'required|numeric|min:0',
                'satuan'        => 'required|string|max:30',
                'modal'         => 'required|numeric|min:0',
            ]);

            $item = Item::findOrFail($id);

            // Ambil diskon lama dari FSN agar tidak di-override
            $diskonLama = $item->diskon ?? 0;

            // ✅ Hitung ulang harga_setelah_diskon dengan harga_jual baru
            $hargaJualBaru      = $validated['harga_jual'];
            $hargaSetelahDiskon = $this->calculateDiscountedPrice($hargaJualBaru, $diskonLama);

            $item->update([
                'tanggal_masuk'        => $validated['tanggal_masuk'],
                'nama_produk'          => $validated['nama_produk'],
                'id_kategori'          => $validated['id_kategori'],
                'id_brand'             => $validated['id_brand'],
                'id_supplier'          => $validated['id_supplier'],
                'harga_jual'           => $hargaJualBaru,
                'stok'                 => $validated['stok'],
                'satuan'               => $validated['satuan'],
                'modal'                => $validated['modal'],
                'harga_setelah_diskon' => $hargaSetelahDiskon, // ✅ Dihitung ulang
            ]);

            Log::info('Item Updated', [
                'id_produk'            => $id,
                'nama_produk'          => $validated['nama_produk'],
                'harga_jual_baru'      => $hargaJualBaru,
                'diskon_preserved'     => $diskonLama,
                'harga_setelah_diskon' => $hargaSetelahDiskon,
            ]);

            return redirect()
                ->route('items.index')
                ->with('success', 'Produk berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Item Update Error', [
                'id_produk' => $id,
                'error'     => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    // ============================================================
    // DESTROY
    // ============================================================
    public function destroy($id)
    {
        try {
            $item     = Item::findOrFail($id);
            $itemName = $item->nama_produk;

            $item->delete();

            Log::info('Item Deleted', [
                'id_produk'   => $id,
                'nama_produk' => $itemName
            ]);

            return redirect()
                ->route('items.index')
                ->with('success', "Produk '{$itemName}' berhasil dihapus.");

        } catch (\Exception $e) {
            Log::error('Item Deletion Error', [
                'id_produk' => $id,
                'error'     => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    // ============================================================
    // CALCULATE FSN (BULK)
    // ============================================================
    public function calculateFSN(Request $request)
    {
        try {
            $periode = $request->input('periode', self::DEFAULT_FSN_PERIOD);
            $this->fsnService->setPeriode($periode);

            $items    = Item::eligibleForFsn()->get();
            $allItems = Item::count();

            $calculated = 0;
            $skipped    = 0;
            $errors     = 0;

            foreach ($items as $item) {
                try {
                    $result = $this->fsnService->calculateSingleItemSmart($item->id_produk);

                    if ($result['fsn'] !== 'NA') {
                        $calculated++;
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('FSN Calculation Error', [
                        'item'  => $item->id_produk,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $message = $this->generateFSNCalculationMessage(
                $calculated, $skipped, $errors, $allItems, $periode
            );

            Log::info('FSN Bulk Calculation', [
                'periode'     => $periode,
                'total_items' => $allItems,
                'eligible'    => $items->count(),
                'calculated'  => $calculated,
                'skipped'     => $skipped,
                'errors'      => $errors
            ]);

            return redirect()
                ->route('items.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('FSN Bulk Calculation Error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Gagal menghitung FSN: ' . $e->getMessage());
        }
    }

    // ============================================================
    // FSN REPORT
    // ============================================================
    public function fsnReport(Request $request)
    {
        $periode = $request->input('periode', self::DEFAULT_FSN_PERIOD);

        $items = Item::with(['kategori', 'brand'])
            ->whereNotNull('tor_value')
            ->orderBy('tor_value', 'desc')
            ->get();

        $summary = $this->getFSNSummary();

        return view('fsn-report', compact('items', 'summary', 'periode'));
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    private function generateProductId()
    {
        $lastItem = Item::orderBy('id_produk', 'desc')->first();
        $number   = $lastItem ? (int) substr($lastItem->id_produk, 2) + 1 : 1;

        return 'BR' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    private function calculateDiscountedPrice($originalPrice, $discountPercentage)
    {
        $discountAmount = $originalPrice * ($discountPercentage / 100);
        return $originalPrice - $discountAmount;
    }

    private function generateFSNCalculationMessage($calculated, $skipped, $errors, $total, $periode)
    {
        $message = "FSN berhasil dihitung untuk {$calculated} produk dari {$total} total produk (periode {$periode} hari).";

        if ($skipped > 0) {
            $message .= " {$skipped} produk dilewati karena belum mencapai " . self::MIN_OBSERVATION_DAYS . " hari observasi.";
        }

        if ($errors > 0) {
            $message .= " {$errors} produk gagal dihitung (cek log untuk detail).";
        }

        return $message;
    }

    private function getFSNSummary()
    {
        return [
            'fast_moving'   => Item::fastMoving()->count(),
            'slow_moving'   => Item::slowMoving()->count(),
            'non_moving'    => Item::nonMoving()->count(),
            'not_analyzed'  => Item::notAnalyzed()->count(),
            'with_discount' => Item::where('diskon', '>', 0)->count(),
            'total'         => Item::count(),
        ];
    }
}