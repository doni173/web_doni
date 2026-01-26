<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->char('id_produk', 5)->nullable(); // Mengaitkan dengan tabel items, bisa bernilai null
            $table->char('id_service', 5)->nullable(); // Mengaitkan dengan tabel services, bisa bernilai null
            $table->integer('jumlah'); // Jumlah barang dalam cart
            $table->decimal('harga', 10, 2); // Harga barang
            $table->decimal('harga_setelah_diskon', 10, 2); // Harga setelah diskon
            $table->timestamps();

            // Menambahkan foreign keys (akan memeriksa ID produk atau layanan yang valid)
            $table->foreign('id_produk')->references('id_produk')->on('items')->onDelete('cascade');
            $table->foreign('id_service')->references('id_service')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
