<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Item extends Model
{
    use HasFactory;

    protected $table      = 'items';
    protected $primaryKey = 'id_produk';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'id_produk',
        'nama_produk',
        'tanggal_masuk',
        'id_kategori',
        'id_brand',
        'id_supplier',
        'satuan',
        'stok',
        'modal',
        'harga_jual',
        'diskon',
        'harga_setelah_diskon',
        'FSN',
        'tor_value',
        'last_fsn_calculation',
        'consecutive_n_months',
    ];

    protected $casts = [
        'tanggal_masuk'        => 'date',
        'last_fsn_calculation' => 'datetime',
        'tor_value'            => 'decimal:2',
        'diskon'               => 'decimal:2',
        'harga_setelah_diskon' => 'decimal:2',
    ];

    // ============================================================
    // ACCESSORS
    // ============================================================

    /**
     * Umur barang dalam hari sejak tanggal masuk.
     */
    public function getUmurHariAttribute(): int
    {
        if (!$this->tanggal_masuk) {
            return 0;
        }

        return Carbon::parse($this->tanggal_masuk)->diffInDays(now());
    }

    // ============================================================
    // SCOPES
    // ============================================================

    /**
     * Barang yang eligible untuk analisis FSN (umur >= 30 hari).
     */
    public function scopeEligibleForFsn($query)
    {
        return $query->whereNotNull('tanggal_masuk')
                     ->whereRaw('DATEDIFF(NOW(), tanggal_masuk) >= 30');
    }

    /** Barang Fast Moving. */
    public function scopeFastMoving($query)
    {
        return $query->where('FSN', 'F');
    }

    /** Barang Slow Moving. */
    public function scopeSlowMoving($query)
    {
        return $query->where('FSN', 'S');
    }

    /** Barang Non Moving. */
    public function scopeNonMoving($query)
    {
        return $query->where('FSN', 'N');
    }

    /** Barang yang belum dianalisis (NA). */
    public function scopeNotAnalyzed($query)
    {
        return $query->where('FSN', 'NA');
    }

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

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