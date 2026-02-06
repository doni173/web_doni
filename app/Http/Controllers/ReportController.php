<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Menampilkan laporan penjualan
     */
    public function index(Request $request)
    {
        // Set timezone dan locale
        Carbon::setLocale('id');
        
        $reportType = $request->get('report_type', 'daily');
        
        // Log untuk debugging
        Log::info('=== REPORT GENERATION ===');
        Log::info('Report Type: ' . $reportType);
        Log::info('Current DateTime: ' . Carbon::now('Asia/Jakarta')->toDateTimeString());
        
        // Query dasar
        $query = Sale::with(['user', 'customer', 'saleDetails.product']);
        
        // Filter berdasarkan tipe laporan
        switch ($reportType) {
            case 'daily':
                $date = $request->get('date', Carbon::today('Asia/Jakarta')->toDateString());
                Log::info('Daily Report - Date: ' . $date);
                $query->whereDate('tanggal_transaksi', '=', $date);
                break;
                
            case 'custom':
                $startDate = $request->get('start_date', Carbon::today('Asia/Jakarta')->startOfMonth()->toDateString());
                $endDate = $request->get('end_date', Carbon::today('Asia/Jakarta')->toDateString());
                Log::info('Custom Report - Start: ' . $startDate . ', End: ' . $endDate);
                $query->whereDate('tanggal_transaksi', '>=', $startDate)
                      ->whereDate('tanggal_transaksi', '<=', $endDate);
                break;
                
            case 'monthly':
                $month = $request->get('month', Carbon::today('Asia/Jakarta')->format('Y-m'));
                $parsedMonth = Carbon::parse($month, 'Asia/Jakarta');
                Log::info('Monthly Report - Month: ' . $parsedMonth->format('Y-m'));
                $query->whereYear('tanggal_transaksi', $parsedMonth->year)
                      ->whereMonth('tanggal_transaksi', $parsedMonth->month);
                break;
                
            case 'yearly':
                $year = $request->get('year', Carbon::today('Asia/Jakarta')->year);
                Log::info('Yearly Report - Year: ' . $year);
                $query->whereYear('tanggal_transaksi', $year);
                break;
        }
        
        // Ambil data penjualan dengan detail yang sudah terfilter
        // PERBAIKAN: Urutkan berdasarkan waktu lengkap, bukan hanya tanggal
        $sales = $query->orderBy('tanggal_transaksi', 'desc')
                       ->orderBy('created_at', 'desc')
                       ->get();
        
        Log::info('Total Sales Found: ' . $sales->count());
        
        // Hitung total penjualan
        $totalPenjualan = $sales->sum('total_belanja');
        
        // Hitung total keuntungan
        $totalKeuntungan = $this->calculateProfit($sales);
        
        Log::info('Total Penjualan: Rp ' . number_format($totalPenjualan, 0, ',', '.'));
        Log::info('Total Keuntungan: Rp ' . number_format($totalKeuntungan, 0, ',', '.'));
        Log::info('=== END REPORT GENERATION ===');
        
        return view('report', compact(
            'sales',
            'reportType',
            'totalPenjualan',
            'totalKeuntungan'
        ));
    }
    
    /**
     * Menghitung total keuntungan dari penjualan
     */
    private function calculateProfit($sales)
    {
        $totalProfit = 0;
        
        foreach ($sales as $sale) {
            // Pastikan saleDetails ada
            if (!$sale->saleDetails) {
                continue;
            }

            foreach ($sale->saleDetails as $detail) {
                // Cek apakah ada produk (bukan service)
                if ($detail->product) {
                    // Ambil harga beli/modal dari produk
                    $hargaBeli = $detail->product->modal ?? $detail->product->harga_beli ?? 0;
                    
                    // Ambil harga jual dari detail penjualan
                    // Prioritas: harga_setelah_diskon > harga > harga_jual produk
                    $hargaJual = $detail->harga_setelah_diskon 
                              ?? $detail->harga 
                              ?? $detail->product->harga_jual 
                              ?? 0;
                    
                    // Jumlah barang yang terjual
                    $jumlah = $detail->jumlah ?? 1;
                    
                    // Hitung profit per item
                    $profit = ($hargaJual - $hargaBeli) * $jumlah;
                    $totalProfit += $profit;
                }
                // Untuk service, tidak ada harga beli, jadi semua adalah profit
                elseif ($detail->service) {
                    $hargaService = $detail->harga_setelah_diskon 
                                 ?? $detail->harga 
                                 ?? $detail->service->harga 
                                 ?? 0;
                    $jumlah = $detail->jumlah ?? 1;
                    
                    // Service profit = harga service (karena tidak ada cost)
                    $totalProfit += ($hargaService * $jumlah);
                }
            }
        }
        
        return $totalProfit;
    }

    /**
     * Export laporan ke PDF
     */
    public function exportPDF(Request $request)
    {
        // TODO: Implementasi export PDF menggunakan library seperti:
        // - barryvdh/laravel-dompdf
        // - mpdf/mpdf
        // - tecnickcom/tcpdf
        
        return redirect()->back()->with('info', 'Fitur export PDF akan segera hadir!');
    }
}