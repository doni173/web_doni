<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;
use App\Services\FSNCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessMonthlyFSN extends Command
{
    /**
     * Nama dan signature command
     *
     * @var string
     */
    protected $signature = 'fsn:process-monthly {--periode=30}';

    /**
     * Deskripsi command
     *
     * @var string
     */
    protected $description = 'Proses FSN Analysis otomatis setiap bulan (periode 30 hari) dengan diskon bertingkat';

    /**
     * FSN Calculation Service
     *
     * @var FSNCalculationService
     */
    protected $fsnService;

    /**
     * Create a new command instance.
     *
     * @param FSNCalculationService $fsnService
     */
    public function __construct(FSNCalculationService $fsnService)
    {
        parent::__construct();
        $this->fsnService = $fsnService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('🚀 Memulai FSN Analysis Bulanan');
        $this->info('📅 Tanggal: ' . now()->format('d-m-Y H:i:s'));
        $this->info('========================================');
        
        // Set periode (default 30 hari)
        $periode = $this->option('periode');
        $this->fsnService->setPeriode($periode);
        $this->info("📊 Periode analisis: {$periode} hari (melihat {$periode} hari ke belakang)");
        
        // ⭐ BARU: Reset barang < 30 hari jadi NA
        $this->info("🧹 Membersihkan data barang yang belum eligible...");
        $resetCount = $this->fsnService->resetIneligibleItems();
        if ($resetCount > 0) {
            $this->warn("⚠️  {$resetCount} barang di-reset ke NA (umur < 30 hari)");
        }
        $this->newLine();
        
        // Ambil semua item
        $items = Item::all();
        $totalItems = $items->count();
        
        $this->info("📦 Total barang: {$totalItems}");
        $this->newLine();
        
        // Counter
        $calculated = 0;
        $skipped = 0;
        $errors = 0;
        
        // Progress bar
        $bar = $this->output->createProgressBar($totalItems);
        $bar->start();
        
        // Log hasil detail
        $results = [];
        
        foreach ($items as $item) {
            try {
                // Cek umur barang
                $umurHari = 0;
                if ($item->tanggal_masuk) {
                    $umurHari = Carbon::parse($item->tanggal_masuk)->diffInDays(now());
                }
                
                if ($umurHari >= 30) {
                    // Hitung FSN
                    $result = $this->fsnService->calculateSingleItem($item->id_produk);
                    $calculated++;
                    
                    $results[] = [
                        'item' => $item->nama_produk,
                        'umur_hari' => $umurHari,
                        'fsn' => $result['fsn'],
                        'tor' => $result['tor_tahunan'] ?? 0,
                        'consecutive_months' => $result['consecutive_n_months'] ?? 0,
                        'diskon' => $result['diskon'] ?? 0,
                        'status' => 'success'
                    ];
                } else {
                    $skipped++;
                    
                    $results[] = [
                        'item' => $item->nama_produk,
                        'fsn' => 'NA',
                        'umur_hari' => $umurHari,
                        'status' => 'skipped',
                        'reason' => 'Belum cukup umur (< 30 hari)'
                    ];
                }
                
            } catch (\Exception $e) {
                $errors++;
                
                $results[] = [
                    'item' => $item->nama_produk,
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
                
                Log::error("FSN Calculation Error", [
                    'item' => $item->nama_produk,
                    'error' => $e->getMessage()
                ]);
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Tampilkan summary
        $this->info('========================================');
        $this->info('✅ FSN Analysis Selesai!');
        $this->info('========================================');
        $this->table(
            ['Kategori', 'Jumlah'],
            [
                ['✅ Berhasil dihitung', $calculated],
                ['⏭️  Dilewati (< 30 hari)', $skipped],
                ['❌ Error', $errors],
                ['📊 Total', $totalItems],
            ]
        );
        
        // Tampilkan breakdown FSN
        $this->newLine();
        $this->info('📊 Breakdown FSN Categories:');
        $fsnBreakdown = [
            ['F (Fast Moving)', Item::where('FSN', 'F')->count()],
            ['S (Slow Moving)', Item::where('FSN', 'S')->count()],
            ['N (Non Moving)', Item::where('FSN', 'N')->count()],
            ['NA (Not Analyzed)', Item::where('FSN', 'NA')->count()],
        ];
        $this->table(['Kategori', 'Jumlah'], $fsnBreakdown);
        
        // Tampilkan breakdown diskon
        $this->newLine();
        $this->info('💰 Breakdown Diskon Bertingkat:');
        $diskonBreakdown = [
            ['Diskon 5% (1 bulan N)', Item::where('diskon', 5)->count()],
            ['Diskon 10% (2 bulan N)', Item::where('diskon', 10)->count()],
            ['Diskon 15% (3+ bulan N)', Item::where('diskon', 15)->count()],
            ['Tanpa Diskon (0%)', Item::where('diskon', 0)->count()],
        ];
        $this->table(['Kategori', 'Jumlah'], $diskonBreakdown);
        
        // Tampilkan barang dengan diskon (top 10)
        $itemsWithDiscount = Item::where('diskon', '>', 0)
            ->orderBy('diskon', 'desc')
            ->limit(10)
            ->get();
        
        if ($itemsWithDiscount->count() > 0) {
            $this->newLine();
            $this->info('🎯 Top 10 Barang dengan Diskon:');
            $discountedItems = $itemsWithDiscount->map(function($item) {
                return [
                    $item->nama_produk,
                    $item->FSN,
                    $item->consecutive_n_months . ' bulan',
                    $item->diskon . '%',
                    'Rp ' . number_format($item->harga_setelah_diskon, 0, ',', '.')
                ];
            })->toArray();
            
            $this->table(
                ['Nama Barang', 'FSN', 'Consecutive N', 'Diskon', 'Harga Diskon'],
                $discountedItems
            );
        }
        
        // Log ke file
        Log::info('FSN Monthly Process Completed', [
            'tanggal' => now()->format('Y-m-d H:i:s'),
            'periode' => $periode,
            'total_items' => $totalItems,
            'calculated' => $calculated,
            'skipped' => $skipped,
            'errors' => $errors,
            'reset_count' => $resetCount,
            'fsn_breakdown' => [
                'F' => Item::where('FSN', 'F')->count(),
                'S' => Item::where('FSN', 'S')->count(),
                'N' => Item::where('FSN', 'N')->count(),
                'NA' => Item::where('FSN', 'NA')->count(),
            ],
            'discount_breakdown' => [
                '5%' => Item::where('diskon', 5)->count(),
                '10%' => Item::where('diskon', 10)->count(),
                '15%' => Item::where('diskon', 15)->count(),
                '0%' => Item::where('diskon', 0)->count(),
            ],
            'results' => $results
        ]);
        
        $this->newLine();
        $this->info('📝 Log lengkap tersimpan di storage/logs/laravel.log');
        $this->info('========================================');
        
        return Command::SUCCESS;
    }
}