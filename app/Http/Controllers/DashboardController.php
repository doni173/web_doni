<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Set timezone untuk memastikan konsistensi
        Carbon::setLocale('id');
        
        // Ambil tanggal hari ini dengan timezone yang benar
        $today = Carbon::today('Asia/Jakarta');
        
        // Debugging (optional - bisa dihapus setelah testing)
        Log::info('=== DASHBOARD DEBUG ===');
        Log::info('Current DateTime: ' . Carbon::now('Asia/Jakarta')->toDateTimeString());
        Log::info('Today Date: ' . $today->toDateString());
        Log::info('Timezone: ' . config('app.timezone'));
        
        // ========================================
        // STATISTIK HARI INI
        // ========================================
        
        // Transaksi Hari Ini
        $transaksiHariIni = Sale::whereDate('tanggal_transaksi', $today)->count();
        // Penjualan Hari Ini (Total Rupiah)
        $penjualanHariIni = Sale::whereDate('tanggal_transaksi', $today)
            ->sum('total_belanja') ?? 0;

        // Keuntungan Hari Ini (Penjualan - Modal)
        $keuntunganHariIni = $this->hitungKeuntunganHariIni($today);

        // Total Barang yang Didiskon Hari Ini
        $barangDiskonHariIni = SaleDetail::whereHas('sale', function($query) use ($today) {
                $query->whereDate('tanggal_transaksi', $today);
            })
            ->where('diskon', '>', 0)
            ->sum('jumlah') ?? 0;

        // ========================================
        // DATA GRAFIK PENJUALAN (7 HARI TERAKHIR)
        // ========================================
        
        $salesData = [];
        $salesLabels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today('Asia/Jakarta')->subDays($i);
            
            // Format label dengan nama hari dalam Bahasa Indonesia
            $salesLabels[] = $this->getDayName($date);
            
            $totalSales = Sale::whereDate('tanggal_transaksi', $date->toDateString())
                ->sum('total_belanja') ?? 0;
            
            $salesData[] = (float) $totalSales;
            

        }

        // ========================================
        // PRODUK PROMO/DISKON
        // ========================================
        
        // Ambil produk dengan stok tinggi yang perlu dipromosikan
        $promoItems = Item::where('stok', '>', 20)
            ->orderBy('stok', 'desc')
            ->take(5)
            ->get();

        // ========================================
        // TRANSAKSI TERBARU
        // ========================================
        
        $transaksiTerbaru = Sale::with('customer')
            ->orderBy('tanggal_transaksi', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();



        return view('dashboard', compact(
            'transaksiHariIni',
            'penjualanHariIni',
            'keuntunganHariIni',
            'barangDiskonHariIni',
            'salesData',
            'salesLabels',
            'promoItems',
            'transaksiTerbaru'
        ));
    }

    /**
     * Menghitung keuntungan hari ini
     * Keuntungan = Harga Jual - Harga Modal
     */
    private function hitungKeuntunganHariIni($today = null)
    {
        if ($today === null) {
            $today = Carbon::today('Asia/Jakarta');
        }

        $totalKeuntungan = 0;

        // Ambil semua detail penjualan hari ini
        $saleDetails = SaleDetail::whereHas('sale', function($query) use ($today) {
                $query->whereDate('tanggal_transaksi', $today);
            })
            ->with('produk')
            ->get();

        foreach ($saleDetails as $detail) {
            if ($detail->produk) {
                // Hitung keuntungan per item
                // Keuntungan = (Harga Setelah Diskon - Modal) * Jumlah
                $hargaJual = $detail->harga_setelah_diskon;
                $hargaModal = $detail->produk->modal;
                $jumlah = $detail->jumlah;
                
                $keuntunganItem = ($hargaJual - $hargaModal) * $jumlah;
                $totalKeuntungan += $keuntunganItem;
            }
        }

        return $totalKeuntungan;
    }

    /**
     * Helper function untuk mendapatkan nama hari dalam Bahasa Indonesia
     */
    private function getDayName($date)
    {
        $days = [
            'Sunday' => 'Min',
            'Monday' => 'Sen',
            'Tuesday' => 'Sel',
            'Wednesday' => 'Rab',
            'Thursday' => 'Kam',
            'Friday' => 'Jum',
            'Saturday' => 'Sab'
        ];
        
        return $days[$date->englishDayOfWeek] ?? $date->format('D');
    }

    /**
     * Method untuk clear cache (optional - bisa diakses via route)
     */
    public function clearDashboardCache()
    {
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('view:clear');
        
        return redirect()->route('dashboard')->with('success', 'Cache berhasil dibersihkan!');
    }
}