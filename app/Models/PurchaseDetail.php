<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $table = 'pembelian_detail';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'id_pembelian',
        'id_produk',
        'jumlah',
        'harga_beli',
        'subtotal'
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_beli' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Relasi ke pembelian utama
     */
  public function pembelian()
{
    return $this->belongsTo(Purchase::class, 'id_pembelian', 'id_pembelian');
}

    /**
     * Relasi ke produk
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    /**
     * Accessor untuk format harga beli
     */
    public function getHargaBeliRupiahAttribute()
    {
        return 'Rp ' . number_format($this->harga_beli, 0, ',', '.');
    }

    /**
     * Accessor untuk format subtotal
     */
    public function getSubtotalRupiahAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }
}