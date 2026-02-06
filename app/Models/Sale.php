<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sale extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'id_penjualan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_penjualan',
        'id_user',
        'id_pelanggan',
        'total_belanja',
        'total_bayar',
        'jumlah_bayar',
        'kembalian',
        'tanggal_transaksi',
    ];

    /**
     * PENTING: Cast tanggal_transaksi ke datetime
     * Ini memastikan Carbon dapat handle timezone dengan benar
     */
    protected $casts = [
        'tanggal_transaksi' => 'datetime',
        'total_belanja' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
    ];

    /**
     * Set timezone default untuk dates
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class, 'id_penjualan', 'id_penjualan');
    }

    /**
     * Accessor untuk mendapatkan tanggal dengan format Indonesia
     */
    public function getTanggalFormatAttribute()
    {
        return Carbon::parse($this->tanggal_transaksi)
            ->timezone('Asia/Jakarta')
            ->translatedFormat('d F Y');
    }

    /**
     * Accessor untuk mendapatkan jam transaksi
     */
    public function getJamTransaksiAttribute()
    {
        return Carbon::parse($this->tanggal_transaksi)
            ->timezone('Asia/Jakarta')
            ->format('H:i');
    }

    /**
     * Accessor untuk mendapatkan tanggal dan jam lengkap
     */
    public function getTanggalLengkapAttribute()
    {
        return Carbon::parse($this->tanggal_transaksi)
            ->timezone('Asia/Jakarta')
            ->translatedFormat('d F Y H:i');
    }
}