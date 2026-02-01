<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'pembelian';
    protected $primaryKey = 'id_pembelian';
    public $timestamps = true;

    protected $fillable = [
        'tanggal_pembelian',
        'nama_supplier',
        'nomor_invoice',
        'total_pembelian',
        'status',
        'created_by',
        'notes'
    ];

    protected $casts = [
        'tanggal_pembelian' => 'date',
        'total_pembelian' => 'decimal:2',
    ];

    /**
     * Relasi ke detail pembelian
     */
   public function details()
{
    return $this->hasMany(PurchaseDetail::class, 'id_pembelian', 'id_pembelian');
}


    /**
     * Relasi ke user yang membuat transaksi
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Accessor untuk format tanggal Indonesia
     */
    public function getTanggalIndonesiaAttribute()
    {
        return $this->tanggal_pembelian->format('d/m/Y');
    }

    /**
     * Accessor untuk format rupiah
     */
    public function getTotalRupiahAttribute()
    {
        return 'Rp ' . number_format($this->total_pembelian, 0, ',', '.');
    }

    /**
     * Scope untuk filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_pembelian', [$startDate, $endDate]);
    }

    /**
     * Scope untuk filter by supplier
     */
    public function scopeBySupplier($query, $supplier)
    {
        return $query->where('nama_supplier', 'like', '%' . $supplier . '%');
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}