<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
    
    protected $table = 'sales';
    protected $primaryKey = 'id_penjualan'; 
    public $incrementing = false; // FALSE karena kita generate manual
    protected $keyType = 'string'; // STRING karena id_penjualan char(5)
    
    protected $fillable = [
        'id_penjualan',
        'id_user',
        'id_pelanggan',
        'total_belanja',
        'total_bayar',
        'jumlah_bayar',
        'kembalian',
        'tanggal_transaksi'
    ];
    
     public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
    
    public $timestamps = true;

     public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_pelanggan', 'id_pelanggan');
    }
    
    
    // Relasi ke SaleDetail
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class, 'id_penjualan', 'id_penjualan');
    }
}