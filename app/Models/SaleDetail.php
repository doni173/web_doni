<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;
    
    protected $table = 'sale_details';
    protected $primaryKey = 'id_detail_penjualan';
    public $incrementing = false; // FALSE karena kita generate manual
    protected $keyType = 'string'; // STRING karena id_detail_penjualan char(5)
    
    protected $fillable = [
        'id_detail_penjualan',
        'id_penjualan',
        'id_produk',
        'id_service',
        'jumlah',
        'diskon',
        'harga_setelah_diskon',
    ];
    
    public $timestamps = true;

    public function produk()
    {
        return $this->belongsTo(Item::class, 'id_produk', 'id_produk');  // id_produk adalah foreign key
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'id_service', 'id'); // id_service adalah foreign key
    }

    
    // Relasi ke Sale
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'id_penjualan', 'id_penjualan');
    }
}