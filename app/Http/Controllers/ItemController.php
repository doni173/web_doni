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
    /**
     * FSN Calculation Service instance
     *
     * @var FSNCalculationService
     */
    protected $fsnService;

    /**
     * Minimum days required for FSN calculation
     *
     * @var int
     */
    const MIN_OBSERVATION_DAYS = 30;

    /**
     * Default FSN calculation period in days
     *
     * @var int
     */
    const DEFAULT_FSN_PERIOD = 90;

    /**
     * Create a new controller instance
     *
     * @param FSNCalculationService $fsnService
     */
    public function __construct(FSNCalculationService $fsnService)
    {
        $this->fsnService = $fsnService;
    }

    /**
     * Display a listing of items
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
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
        $brands = Brand::orderBy('brand')->get();
        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('item', compact(
            'items',
            'searchQuery',
            'categories',
            'brands',
            'suppliers'
        ));
    }

    /**
     * Store a newly created item in storage
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_produk' => 'required|string|max:100',
                'tanggal_masuk' => 'nullable|date|before_or_equal:today',
                'harga_jual' => 'required|numeric|min:0',
                'id_kategori' => 'required|exists:categories,id_kategori',
                'id_brand' => 'required|exists:brands,id_brand',
                'id_supplier' => 'required|exists:suppliers,id_supplier',
                'stok' => 'required|numeric|min:0',
                'satuan' => 'required|string|max:30',
                'modal' => 'required|numeric|min:0',
                'diskon' => 'required|numeric|min:0|max:100',
            ]);

            $idProduk = $this->generateProductId();
            
            // ✅ PERBAIKAN: Diskon awal = 0 (akan dihitung otomatis oleh FSN)
            $hargaSetelahDiskon = $validated['harga_jual']; // Tanpa diskon dulu

            Item::create([
                'id_produk' => $idProduk,
                'tanggal_masuk' => $validated['tanggal_masuk'] ?? now(),
                'nama_produk' => $validated['nama_produk'],
                'id_kategori' => $validated['id_kategori'],
                'id_brand' => $validated['id_brand'],
                'id_supplier' => $validated['id_supplier'],
                'harga_jual' => $validated['harga_jual'],
                'stok' => $validated['stok'],
                'satuan' => $validated['satuan'],
                'modal' => $validated['modal'],
                'FSN' => 'NA',
                'tor_value' => null,
                'diskon' => 0, // ✅ Set 0, akan diupdate otomatis
                'harga_setelah_diskon' => $hargaSetelahDiskon,
                'consecutive_n_months' => 0,
            ]);

            Log::info('Item Created', [
                'id_produk' => $idProduk,
                'nama_produk' => $validated['nama_produk'],
                'tanggal_masuk' => $validated['tanggal_masuk'] ?? now()
            ]);

            return redirect()
                ->route('items.index')
                ->with('success', 'Produk berhasil ditambahkan. FSN akan dihitung setelah periode observasi ' . self::MIN_OBSERVATION_DAYS . ' hari.');
                
        } catch (\Exception $e) {
            Log::error('Item Creation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified item in storage
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nama_produk' => 'required|string|max:100',
                'tanggal_masuk' => 'required|date|before_or_equal:today',
                'harga_jual' => 'required|numeric|min:0',
                'id_kategori' => 'required|exists:categories,id_kategori',
                'id_brand' => 'required|exists:brands,id_brand',
                'id_supplier' => 'required|exists:suppliers,id_supplier',
                'stok' => 'required|numeric|min:0',
                'satuan' => 'required|string|max:30',
                'modal' => 'required|numeric|min:0',
                // ❌ PERBAIKAN: Hapus validasi diskon karena tidak digunakan (diskon diatur otomatis oleh FSN)
                // 'diskon' => 'required|numeric|min:0|max:100',
            ]);

            $item = Item::findOrFail($id);

            // ✅ PERBAIKAN: Simpan FSN dan diskon lama
            $oldFSN = $item->FSN;
            $oldDiskon = $item->diskon;
            $oldConsecutive = $item->consecutive_n_months;

            $item->update([
                'tanggal_masuk' => $validated['tanggal_masuk'],
                'nama_produk' => $validated['nama_produk'],
                'id_kategori' => $validated['id_kategori'],
                'id_brand' => $validated['id_brand'],
                'id_supplier' => $validated['id_supplier'],
                'harga_jual' => $validated['harga_jual'],
                'stok' => $validated['stok'],
                'satuan' => $validated['satuan'],
                'modal' => $validated['modal'],
                // ✅ PERBAIKAN: Diskon manual TIDAK BOLEH override diskon FSN otomatis
                // Diskon diatur otomatis berdasarkan FSN analysis
            ]);

            Log::info('Item Updated', [
                'id_produk' => $id,
                'nama_produk' => $validated['nama_produk'],
                'fsn_preserved' => $oldFSN,
                'diskon_preserved' => $oldDiskon
            ]);

            return redirect()
                ->route('items.index')
                ->with('success', 'Produk berhasil diperbarui. Diskon FSN otomatis tetap terjaga.');

        } catch (\Exception $e) {
            Log::error('Item Update Error', [
                'id_produk' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified item from storage
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $item = Item::findOrFail($id);
            $itemName = $item->nama_produk;
            
            $item->delete();

            Log::info('Item Deleted', [
                'id_produk' => $id,
                'nama_produk' => $itemName
            ]);

            return redirect()
                ->route('items.index')
                ->with('success', "Produk '{$itemName}' berhasil dihapus.");
                
        } catch (\Exception $e) {
            Log::error('Item Deletion Error', [
                'id_produk' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    /**
     * ✅ PERBAIKAN: Calculate FSN untuk semua item yang eligible
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function calculateFSN(Request $request)
    {
        try {
            $periode = $request->input('periode', self::DEFAULT_FSN_PERIOD);
            $this->fsnService->setPeriode($periode);

            // ✅ Gunakan scope eligibleForFsn
            $items = Item::eligibleForFsn()->get();
            $allItems = Item::count();
            
            $calculated = 0;
            $skipped = 0;
            $errors = 0;

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
                        'item' => $item->id_produk,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $message = $this->generateFSNCalculationMessage($calculated, $skipped, $errors, $allItems, $periode);

            Log::info('FSN Bulk Calculation', [
                'periode' => $periode,
                'total_items' => $allItems,
                'eligible' => $items->count(),
                'calculated' => $calculated,
                'skipped' => $skipped,
                'errors' => $errors
            ]);

            return redirect()
                ->route('items.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            Log::error('FSN Bulk Calculation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal menghitung FSN: ' . $e->getMessage());
        }
    }

    /**
     * Display FSN analysis report
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
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

    /**
     * Generate new product ID (BR001, BR002, etc.)
     *
     * @return string
     */
    private function generateProductId()
    {
        $lastItem = Item::orderBy('id_produk', 'desc')->first();
        $number = $lastItem ? (int) substr($lastItem->id_produk, 2) + 1 : 1;
        
        return 'BR' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate price after discount
     *
     * @param float $originalPrice
     * @param float $discountPercentage
     * @return float
     */
    private function calculateDiscountedPrice($originalPrice, $discountPercentage)
    {
        $discountAmount = $originalPrice * ($discountPercentage / 100);
        return $originalPrice - $discountAmount;
    }

    /**
     * ✅ PERBAIKAN: Generate pesan hasil perhitungan FSN yang lebih informatif
     *
     * @param int $calculated
     * @param int $skipped
     * @param int $errors
     * @param int $total
     * @param int $periode
     * @return string
     */
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

    /**
     * Get FSN classification summary
     *
     * @return array
     */
    private function getFSNSummary()
    {
        return [
            'fast_moving' => Item::fastMoving()->count(),
            'slow_moving' => Item::slowMoving()->count(),
            'non_moving' => Item::nonMoving()->count(),
            'not_analyzed' => Item::notAnalyzed()->count(),
            'with_discount' => Item::where('diskon', '>', 0)->count(),
            'total' => Item::count(),
        ];
    }
}