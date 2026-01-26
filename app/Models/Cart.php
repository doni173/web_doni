<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    // Menentukan kolom yang dapat diisi
    protected $fillable = [
        'id_user',
        'tanggal_transaksi',
        'nama_pelanggan',
        'total_belanja',
        'jumlah_bayar',
        'kembalian'
    ];

    // Relasi dengan cart_items
    public function items()
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
