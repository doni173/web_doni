<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    protected $table = 'sale_details';
    protected $primaryKey = 'id_detail_penjualan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_detail_penjualan',
        'id_penjualan',
        'id_produk',
        'id_service',
        'jumlah',
        'diskon',
        'harga',
        'harga_setelah_diskon',
    ];

    /**
     * Relasi ke tabel Sale (Penjualan)
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'id_penjualan', 'id_penjualan');
    }

    /**
     * Relasi ke tabel Item (Produk)
     * ✅ PERBAIKAN: Gunakan 'product' bukan 'produk'
     */
    public function product()
    {
        return $this->belongsTo(Item::class, 'id_produk', 'id_produk');
    }

    /**
     * Relasi ke tabel Item (Produk) - Alias alternatif
     * Untuk backward compatibility jika ada kode yang masih pakai 'produk'
     */
    public function produk()
    {
        return $this->belongsTo(Item::class, 'id_produk', 'id_produk');
    }

    /**
     * Relasi ke tabel Service (Layanan)
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'id_service', 'id_service');
    }
}