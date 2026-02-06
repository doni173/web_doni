<?php

namespace App\Services;

use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FSNCalculationService
{
    protected $periodeDays = 30;

    public function setPeriode($days)
    {
        $this->periodeDays = $days;
        return $this;
    }

    /**
     * Hitung FSN untuk SEMUA BARANG YANG AKTIF
     */
    public function calculateAllItems()
    {
        $items = Item::where('fsn_active', 1)->get();
        $results = [];

        foreach ($items as $item) {
            $results[] = $this->calculateSingleItem($item->id_produk);
        }

        return $results;
    }

    public function calculateSingleItemSmart($itemId)
    {
        return $this->calculateSingleItem($itemId);
    }

    /**
     * Hitung FSN satu barang
     */
    public function calculateSingleItem($itemId)
    {
        $item = Item::where('id_produk', $itemId)->firstOrFail();

        /* ===============================
           1. CEK FSN AKTIF
        ================================ */
        if (!$item->fsn_active) {
            return [
                'item' => $item->nama_produk,
                'fsn' => 'NA',
                'keterangan' => 'FSN belum diaktifkan'
            ];
        }

        /* ===============================
           2. CEK UMUR BARANG
        ================================ */
        $umurHari = 0;
        if ($item->tanggal_masuk) {
            $umurHari = Carbon::parse($item->tanggal_masuk)->diffInDays(now());
        }

        if ($umurHari < 30) {
            return [
                'item' => $item->nama_produk,
                'fsn' => 'NA',
                'keterangan' => 'Belum cukup umur observasi'
            ];
        }

        /* ===============================
           3. PERIODE ANALISIS
        ================================ */
        $endDate   = Carbon::now();
        $startDate = Carbon::now()->subDays($this->periodeDays);

        /* ===============================
           4. HITUNG KOMPONEN FSN
        ================================ */
        $paw = $this->getPersediaanAwal($item, $startDate);
        $pms = $this->getBarangMasuk($item->id_produk, $startDate, $endDate);
        $pmk = $this->getBarangTerjual($item->id_produk, $startDate, $endDate);

        $pak = $paw + $pms - $pmk;
        $prt = ($paw + $pak) / 2;

        /* ===============================
           5. JIKA TIDAK ADA PENJUALAN
        ================================ */
        if ($pmk == 0) {
            $item->update([
                'FSN' => 'N',
                'tor_value' => 0,
                'last_fsn_calculation' => now()
            ]);

            return [
                'item' => $item->nama_produk,
                'fsn' => 'N',
                'keterangan' => 'Tidak ada penjualan'
            ];
        }

        if ($prt <= 0) {
            $prt = 0.01;
        }

        /* ===============================
           6. HITUNG TOR
        ================================ */
        $torParsial = $pmk / $prt;
        $wsp        = $this->periodeDays / $torParsial;
        $torTahunan = 365 / $wsp;

        /* ===============================
           7. TENTUKAN FSN
        ================================ */
        $fsn = $this->getFSNCategory($torTahunan);

        /* ===============================
           8. SIMPAN KE DATABASE
        ================================ */
        $item->update([
            'FSN' => $fsn,
            'tor_value' => round($torTahunan, 2),
            'last_fsn_calculation' => now()
        ]);

        return [
            'item' => $item->nama_produk,
            'paw' => $paw,
            'pms' => $pms,
            'pmk' => $pmk,
            'pak' => $pak,
            'prt' => $prt,
            'tor_parsial' => round($torParsial, 2),
            'tor_tahunan' => round($torTahunan, 2),
            'fsn' => $fsn,
            'keterangan' => 'Berhasil dihitung'
        ];
    }

    /* ===============================
       HELPER FUNCTIONS
    ================================ */

    private function getPersediaanAwal($item, $startDate)
    {
        $penjualan = $this->getBarangTerjual($item->id_produk, $startDate, now());
        $pembelian = $this->getBarangMasuk($item->id_produk, $startDate, now());

        return $item->stok + $penjualan - $pembelian;
    }

    private function getBarangMasuk($itemId, $startDate, $endDate)
    {
        return DB::table('purchase_details')
            ->join('purchases', 'purchase_details.id_pembelian', '=', 'purchases.id_pembelian')
            ->where('purchase_details.id_produk', $itemId)
            ->whereBetween('purchases.tgl_pembelian', [$startDate, $endDate])
            ->sum('purchase_details.jumlah_beli');
    }

    private function getBarangTerjual($itemId, $startDate, $endDate)
    {
        return DB::table('sale_details')
            ->join('sales', 'sale_details.id_penjualan', '=', 'sales.id_penjualan')
            ->where('sale_details.id_produk', $itemId)
            ->whereBetween('sales.tanggal_transaksi', [$startDate, $endDate])
            ->sum('sale_details.jumlah');
    }

    private function getFSNCategory($tor)
    {
        if ($tor > 3) {
            return 'F';
        } elseif ($tor >= 1) {
            return 'S';
        } else {
            return 'N';
        }
    }
}
