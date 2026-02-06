<?php

namespace App\Services;

use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoDiscountService
{
    /**
     * Tabel diskon berdasarkan jumlah hari non-moving
     */
    private $discountRules = [
        30  => 5,   // 0-30 hari   → 5%
        60  => 10,  // 31-60 hari  → 10%
        90  => 15,  // 61-90 hari  → 15%
        120 => 20,  // 91-120 hari → 20%
        150 => 25,  // 121-150 hari→ 25%
        999 => 30,  // 151+ hari   → 30% (MAX)
    ];

    /**
     * Update diskon otomatis untuk satu item berdasarkan status FSN
     */
    public function updateDiscount(Item $item)
    {
        // Jika auto discount tidak aktif, skip
        if (!$item->auto_discount_enabled) {
            return;
        }

        $oldFSN = $item->getOriginal('FSN'); // FSN sebelum update
        $newFSN = $item->FSN;                 // FSN setelah update

        // CASE 1: Barang berubah menjadi N (dari F/S/NA ke N)
        if ($newFSN === 'N' && $oldFSN !== 'N') {
            $this->startNonMovingTracking($item);
        }
        
        // CASE 2: Barang masih N (terus-menerus)
        elseif ($newFSN === 'N' && $oldFSN === 'N') {
            $this->increaseDiscount($item);
        }
        
        // CASE 3: Barang berubah menjadi S atau F (dari N ke S/F)
        elseif (($newFSN === 'S' || $newFSN === 'F') && $oldFSN === 'N') {
            $this->resetDiscount($item);
        }

        $item->save();
    }

    /**
     * Mulai tracking barang non-moving
     */
    private function startNonMovingTracking(Item $item)
    {
        $item->first_non_moving_date = Carbon::now();
        $item->non_moving_days = 0;
        $item->diskon = 5; // Diskon awal 5%
        
        $this->updateHargaSetelahDiskon($item);

        Log::info("Item {$item->nama_produk} mulai non-moving. Diskon set ke 5%");
    }

    /**
     * Tingkatkan diskon jika masih N
     */
    private function increaseDiscount(Item $item)
    {
        if (!$item->first_non_moving_date) {
            // Jika tidak ada tanggal mulai, set sekarang
            $this->startNonMovingTracking($item);
            return;
        }

        // Hitung berapa hari sudah non-moving
        $daysSinceNonMoving = Carbon::now()->diffInDays($item->first_non_moving_date);
        $item->non_moving_days = $daysSinceNonMoving;

        // Tentukan diskon berdasarkan jumlah hari
        $newDiscount = $this->calculateDiscount($daysSinceNonMoving);

        // Update diskon jika ada perubahan
        if ($item->diskon != $newDiscount) {
            $oldDiscount = $item->diskon;
            $item->diskon = $newDiscount;
            
            $this->updateHargaSetelahDiskon($item);

            Log::info("Item {$item->nama_produk} non-moving selama {$daysSinceNonMoving} hari. Diskon dinaikkan dari {$oldDiscount}% ke {$newDiscount}%");
        }
    }

    /**
     * Reset diskon saat barang menjadi S atau F
     */
    private function resetDiscount(Item $item)
    {
        $oldDiscount = $item->diskon;
        
        $item->diskon = 0;
        $item->first_non_moving_date = null;
        $item->non_moving_days = 0;
        
        $this->updateHargaSetelahDiskon($item);

        Log::info("Item {$item->nama_produk} berubah menjadi {$item->FSN}. Diskon reset dari {$oldDiscount}% ke 0%");
    }

    /**
     * Hitung diskon berdasarkan jumlah hari non-moving
     */
    private function calculateDiscount($days)
    {
        foreach ($this->discountRules as $threshold => $discount) {
            if ($days <= $threshold) {
                return $discount;
            }
        }
        
        return 30; // Max diskon
    }

    /**
     * Update harga setelah diskon
     */
    private function updateHargaSetelahDiskon(Item $item)
    {
        $hargaSetelahDiskon = $item->harga_jual - ($item->harga_jual * ($item->diskon / 100));
        $item->harga_setelah_diskon = $hargaSetelahDiskon;
    }

    /**
     * Proses semua item untuk update diskon
     * Dipanggil oleh scheduler
     */
    public function processAllItems()
    {
        $items = Item::where('auto_discount_enabled', true)->get();
        
        $updated = 0;
        foreach ($items as $item) {
            $oldDiscount = $item->diskon;
            $this->updateDiscount($item);
            
            if ($oldDiscount != $item->diskon) {
                $updated++;
            }
        }

        Log::info("Auto discount processed: {$updated} items updated");
        
        return $updated;
    }
}