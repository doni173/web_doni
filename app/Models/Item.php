<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    // Tentukan primary key jika berbeda dengan default (id)
    protected $primaryKey = 'id_produk';
    public $incrementing = false;  // Menyatakan bahwa id_produk bukan auto increment
    protected $keyType = 'string'; // Karena id_produk bertipe string (misalnya 'BR001')

    // Daftar kolom yang boleh diisi mass-assignable
    protected $fillable = [
        'id_produk',
        'nama_produk',
        'id_kategori',
        'id_brand',
        'satuan',
        'modal',
        'harga_jual',
        'stok',
        'FSN',
        'diskon',
        'harga_setelah_diskon',  // Menambahkan kolom harga_setelah_diskon ke dalam fillable
    ];

    // Relasi dengan kategori
    public function kategori()
    {
        // 'id_kategori' di tabel 'items' merujuk ke 'id_kategori' di tabel 'categories'
        return $this->belongsTo(Category::class, 'id_kategori', 'id_kategori');
    }

    // Relasi dengan brand
    public function brand()
    {
        // 'id_brand' di tabel 'items' merujuk ke 'id_brand' di tabel 'brands'
        return $this->belongsTo(Brand::class, 'id_brand', 'id_brand');
    }

    // Aksesori untuk menghitung harga setelah diskon
    public function getHargaSetelahDiskonAttribute()
    {
        // Menghitung harga setelah diskon jika diskon tersedia
        return $this->harga_jual - ($this->harga_jual * ($this->diskon / 100));
    }

    // Opsional: Menggunakan mutator untuk menyimpan harga setelah diskon saat menyimpan data
    public function setHargaSetelahDiskonAttribute($value)
    {
        // Ini akan otomatis menghitung harga_setelah_diskon berdasarkan harga_jual dan diskon yang diterapkan
        $this->attributes['harga_setelah_diskon'] = $this->harga_jual - ($this->harga_jual * ($this->diskon / 100));
    }
}
