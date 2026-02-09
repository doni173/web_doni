<?php

namespace App\Services;

use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FSNCalculationService
{
    protected $periodeDays = 30; // ⭐ Default 30 hari

    /**
     * Set periode analisis dalam hari
     */
    public function setPeriode($days)
    {
        $this->periodeDays = $days;
        return $this;
    }

    /**
     * Hitung FSN untuk SEMUA BARANG
     */
    public function calculateAllItems()
    {
        $items = Item::all();
        $results = [];

        foreach ($items as $item) {
            $results[] = $this->calculateSingleItem($item->id_produk);
        }

        return $results;
    }

    /**
     * Hitung FSN satu barang (wrapper untuk konsistensi)
     */
    public function calculateSingleItemSmart($itemId)
    {
        return $this->calculateSingleItem($itemId);
    }

    /**
     * PERHITUNGAN FSN DENGAN DISKON BERTINGKAT OTOMATIS
     */
    public function calculateSingleItem($itemId)
    {
        $item = Item::where('id_produk', $itemId)->firstOrFail();

        /* ===============================
           1. CEK UMUR BARANG (MIN 30 HARI)
        ================================ */
        $umurHari = 0;
        if ($item->tanggal_masuk) {
            $umurHari = Carbon::parse($item->tanggal_masuk)->diffInDays(now());
        }

        // Jika barang belum berumur 30 hari, skip perhitungan
        if ($umurHari < 30) {
            $item->update([
                'FSN' => 'NA',
                'tor_value' => null,
                'last_fsn_calculation' => now(),
                'consecutive_n_months' => 0,
                'diskon' => 0,
                'harga_setelah_diskon' => $item->harga_jual,
            ]);

            return [
                'item' => $item->nama_produk,
                'fsn' => 'NA',
                'tor_value' => null,
                'diskon' => 0,
                'consecutive_n_months' => 0,
                'keterangan' => "Belum cukup umur observasi (umur: {$umurHari} hari)"
            ];
        }

        /* ===============================
           2. TENTUKAN PERIODE ANALISIS
        ================================ */
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($this->periodeDays);

        /* ===============================
           3. HITUNG TOTAL PENJUALAN DALAM PERIODE
        ================================ */
        $totalPenjualan = $this->getBarangTerjual($item->id_produk, $startDate, $endDate);

        /* ===============================
           4. HITUNG RATA-RATA STOK
        ================================ */
        $rataRataStok = $this->getRataRataStok($item);

        /* ===============================
           5. VALIDASI DATA - Tidak ada penjualan
        ================================ */
        if ($totalPenjualan == 0) {
            // Simpan FSN lama untuk perbandingan
            $previousFSN = $item->FSN;
            
            $item->update([
                'FSN' => 'N',
                'tor_value' => 0,
                'last_fsn_calculation' => now()
            ]);

            // Update diskon bertingkat
            $this->updateTieredDiscount($item, 'N', $previousFSN);

            return [
                'item' => $item->nama_produk,
                'periode_hari' => $this->periodeDays,
                'total_penjualan' => $totalPenjualan,
                'rata_rata_stok' => $rataRataStok,
                'tor_value' => 0,
                'tor_tahunan' => 0,
                'fsn' => 'N',
                'consecutive_n_months' => $item->consecutive_n_months,
                'diskon' => $item->diskon,
                'keterangan' => 'Tidak ada penjualan dalam periode'
            ];
        }

        // Jika stok rata-rata = 0, set minimal 1
        if ($rataRataStok <= 0) {
            $rataRataStok = 1;
        }

        /* ===============================
           6. HITUNG TOR (TURNOVER RATIO) TAHUNAN
        ================================ */
        $penjualanHarian = $totalPenjualan / $this->periodeDays;
        $penjualanTahunan = $penjualanHarian * 365;
        $torTahunan = $penjualanTahunan / $rataRataStok;

        /* ===============================
           7. TENTUKAN KATEGORI FSN
        ================================ */
        $fsn = $this->getFSNCategory($torTahunan);

        /* ===============================
           8. SIMPAN FSN & UPDATE DISKON BERTINGKAT
        ================================ */
        $previousFSN = $item->FSN;
        
        $item->update([
            'FSN' => $fsn,
            'tor_value' => round($torTahunan, 2),
            'last_fsn_calculation' => now()
        ]);

        // ⭐ Update diskon bertingkat otomatis
        $this->updateTieredDiscount($item, $fsn, $previousFSN);

        /* ===============================
           9. RETURN HASIL DETAIL
        ================================ */
        return [
            'item' => $item->nama_produk,
            'periode_hari' => $this->periodeDays,
            'umur_hari' => $umurHari,
            'total_penjualan' => $totalPenjualan,
            'rata_rata_stok' => round($rataRataStok, 2),
            'penjualan_harian' => round($penjualanHarian, 2),
            'penjualan_tahunan' => round($penjualanTahunan, 2),
            'tor_tahunan' => round($torTahunan, 2),
            'fsn' => $fsn,
            'previous_fsn' => $previousFSN,
            'consecutive_n_months' => $item->consecutive_n_months,
            'diskon' => $item->diskon,
            'keterangan' => 'Berhasil dihitung'
        ];
    }

    /* ===============================
       ⭐ FUNGSI BARU: UPDATE DISKON BERTINGKAT
    ================================ */
    
    /**
     * Update diskon bertingkat berdasarkan status FSN
     */
    private function updateTieredDiscount(Item $item, $currentFSN, $previousFSN)
    {
        if ($currentFSN === 'N') {
            // Barang berstatus N
            
            if ($previousFSN === 'N') {
                // Masih N dari bulan sebelumnya, tambah counter
                $item->consecutive_n_months = $item->consecutive_n_months + 1;
            } else {
                // Baru pertama kali jadi N (atau dari NA/F/S)
                $item->consecutive_n_months = 1;
            }
            
            // Hitung diskon berdasarkan berapa bulan berturut-turut N
            $diskon = $this->calculateDiscountPercentage($item->consecutive_n_months);
            
        } else {
            // Barang BUKAN N (F, S, atau NA)
            // Reset semua
            $item->consecutive_n_months = 0;
            $diskon = 0;
        }
        
        // Update diskon dan harga setelah diskon
        $item->diskon = $diskon;
        $item->harga_setelah_diskon = $this->calculateDiscountedPrice($item->harga_jual, $diskon);
        $item->save();
        
        // Log untuk tracking
        Log::info("Diskon Updated", [
            'item' => $item->nama_produk,
            'fsn' => $currentFSN,
            'previous_fsn' => $previousFSN,
            'consecutive_n_months' => $item->consecutive_n_months,
            'diskon' => $diskon . '%'
        ]);
    }
    
    /**
     * Hitung persentase diskon berdasarkan consecutive_n_months
     */
    private function calculateDiscountPercentage($months)
    {
        if ($months >= 3) {
            return 15.00; // Max 15%
        } elseif ($months == 2) {
            return 10.00; // 10%
        } elseif ($months == 1) {
            return 5.00;  // 5%
        }
        
        return 0.00; // Tidak ada diskon
    }
    
    /**
     * Hitung harga setelah diskon
     */
    private function calculateDiscountedPrice($hargaJual, $diskonPersen)
    {
        $diskonAmount = $hargaJual * ($diskonPersen / 100);
        return $hargaJual - $diskonAmount;
    }

    /* ===============================
       HELPER FUNCTIONS
    ================================ */

    /**
     * Hitung rata-rata stok
     */
    private function getRataRataStok($item)
    {
        $stokSekarang = $item->stok;
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($this->periodeDays);
        
        $totalPembelian = $this->getBarangMasuk($item->id_produk, $startDate, $endDate);
        $totalPenjualan = $this->getBarangTerjual($item->id_produk, $startDate, $endDate);

        $stokAwal = $stokSekarang - $totalPembelian + $totalPenjualan;
        $stokAwal = max(0, $stokAwal);

        $rataRataStok = ($stokAwal + $stokSekarang) / 2;

        return max(1, $rataRataStok);
    }

    /**
     * Hitung total barang masuk dalam periode
     */
    private function getBarangMasuk($itemId, $startDate, $endDate)
    {
        $total = DB::table('purchase_details')
            ->join('purchases', 'purchase_details.id_pembelian', '=', 'purchases.id_pembelian')
            ->where('purchase_details.id_produk', $itemId)
            ->whereBetween('purchases.tgl_pembelian', [$startDate, $endDate])
            ->sum('purchase_details.jumlah_beli');

        return $total ?? 0;
    }

    /**
     * Hitung total barang terjual dalam periode
     */
    private function getBarangTerjual($itemId, $startDate, $endDate)
    {
        $total = DB::table('sale_details')
            ->join('sales', 'sale_details.id_penjualan', '=', 'sales.id_penjualan')
            ->where('sale_details.id_produk', $itemId)
            ->whereNotNull('sale_details.id_produk') // ⭐ Filter hanya produk
            ->whereBetween('sales.tanggal_transaksi', [$startDate, $endDate])
            ->sum('sale_details.jumlah');

        return $total ?? 0;
    }

    /**
     * Tentukan kategori FSN berdasarkan TOR
     */
    private function getFSNCategory($tor)
    {
        if ($tor > 3) {
            return 'F'; // Fast Moving
        } elseif ($tor >= 1) {
            return 'S'; // Slow Moving
        } else {
            return 'N'; // Non Moving
        }
    }

    /**
     * Get detail perhitungan untuk debugging
     */
    public function getCalculationDetails($itemId)
    {
        return $this->calculateSingleItem($itemId);
    }

    /**
     * ⭐ BARU: Reset barang yang belum cukup umur jadi NA
     * 
     * @return int Jumlah barang yang di-reset
     */
    public function resetIneligibleItems()
    {
        $resetCount = 0;
        
        // Ambil barang yang umurnya < 30 hari TAPI FSN bukan NA
        $items = Item::whereNotNull('tanggal_masuk')
            ->whereRaw('DATEDIFF(NOW(), tanggal_masuk) < 30')
            ->where('FSN', '!=', 'NA')
            ->get();
        
        foreach ($items as $item) {
            $umurHari = Carbon::parse($item->tanggal_masuk)->diffInDays(now());
            
            $item->update([
                'FSN' => 'NA',
                'tor_value' => null,
                'consecutive_n_months' => 0,
                'diskon' => 0,
                'harga_setelah_diskon' => $item->harga_jual,
                'last_fsn_calculation' => null
            ]);
            
            Log::info("Reset to NA (umur < 30 hari)", [
                'item' => $item->nama_produk,
                'umur_hari' => $umurHari,
                'old_fsn' => $item->FSN
            ]);
            
            $resetCount++;
        }
        
        return $resetCount;
    }
}