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
        $hargaSetelahDiskon = $this->calculateDiscountedPrice(
            $validated['harga_jual'],
            $validated['diskon']
        );

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
            'diskon' => $validated['diskon'],
            'harga_setelah_diskon' => $hargaSetelahDiskon,
        ]);

        return redirect()
            ->route('items.index')
            ->with('success', 'Produk berhasil ditambahkan. FSN akan dihitung setelah periode observasi.');
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
            'diskon' => 'required|numeric|min:0|max:100',
        ]);

        $item = Item::findOrFail($id);
        $hargaSetelahDiskon = $this->calculateDiscountedPrice(
            $validated['harga_jual'],
            $validated['diskon']
        );

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
            'diskon' => $validated['diskon'],
            'harga_setelah_diskon' => $hargaSetelahDiskon,
        ]);

        return redirect()
            ->route('items.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified item from storage
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return redirect()
            ->route('items.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Calculate FSN for all eligible items
     * Only calculates for items that have been in inventory for at least MIN_OBSERVATION_DAYS
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function calculateFSN(Request $request)
    {
        $periode = $request->input('periode', self::DEFAULT_FSN_PERIOD);
        $this->fsnService->setPeriode($periode);

        $items = Item::all();
        $calculated = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if ($this->isEligibleForFSNCalculation($item)) {
                $this->fsnService->calculateSingleItemSmart($item->id_produk);
                $calculated++;
            } else {
                $skipped++;
            }
        }

        $message = $this->generateFSNCalculationMessage($calculated, $skipped, $periode);

        return redirect()
            ->route('items.index')
            ->with('success', $message);
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
     * Check if item is eligible for FSN calculation
     *
     * @param Item $item
     * @return bool
     */
    private function isEligibleForFSNCalculation(Item $item)
    {
        if (!$item->tanggal_masuk) {
            return false;
        }

        $daysSinceAdded = Carbon::parse($item->tanggal_masuk)->diffInDays(Carbon::now());
        return $daysSinceAdded >= self::MIN_OBSERVATION_DAYS;
    }

    /**
     * Generate FSN calculation result message
     *
     * @param int $calculated
     * @param int $skipped
     * @param int $periode
     * @return string
     */
    private function generateFSNCalculationMessage($calculated, $skipped, $periode)
    {
        $message = "FSN berhasil dihitung untuk {$calculated} produk (periode {$periode} hari).";
        
        if ($skipped > 0) {
            $message .= " {$skipped} produk dilewati karena belum mencapai " . self::MIN_OBSERVATION_DAYS . " hari observasi.";
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
            'fast_moving' => Item::where('FSN', 'F')->count(),
            'slow_moving' => Item::where('FSN', 'S')->count(),
            'non_moving' => Item::where('FSN', 'N')->count(),
            'not_analyzed' => Item::where('FSN', 'NA')->count(),
        ];
    }
}