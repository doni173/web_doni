<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items';
    protected $primaryKey = 'id_produk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_produk',
        'tanggal_masuk',
        'nama_produk',
        'id_kategori',
        'id_brand',
        'id_supplier',
        'harga_jual',
        'stok',
        'stok_awal',
        'satuan',
        'modal',
        'FSN',
        'tor_value',
        'last_fsn_calculation',
        'days_as_n',
        'n_status_started_at',
        'diskon',
        'auto_discount',
        'is_auto_discount_active',
        'harga_setelah_diskon',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
        'last_fsn_calculation' => 'datetime',
        'n_status_started_at' => 'datetime',
        'is_auto_discount_active' => 'boolean',
        'auto_discount' => 'decimal:2',
        'diskon' => 'decimal:2',
    ];

    /**
     * Get total discount (manual + auto)
     */
    public function getTotalDiscountAttribute()
    {
        return $this->diskon + $this->auto_discount;
    }

    /**
     * Calculate final price after all discounts
     */
    public function getFinalPriceAttribute()
    {
        $totalDiscount = $this->total_discount;
        $discountAmount = $this->harga_jual * ($totalDiscount / 100);
        return $this->harga_jual - $discountAmount;
    }

    public function kategori()
    {
        return $this->belongsTo(Category::class, 'id_kategori', 'id_kategori');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'id_brand', 'id_brand');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }
}