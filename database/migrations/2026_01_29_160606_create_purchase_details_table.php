<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_details', function (Blueprint $table) {

            $table->char('id_detail_pembelian',5)->primary();
            $table->char('id_pembelian',5);
            $table->char('id_produk',5);
            $table->char('id_supplier',5);

            $table->integer('stok_lama');
            $table->integer('jumlah_beli');
            $table->integer('stok_baru');
            $table->decimal('harga_beli', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            $table->foreign('id_pembelian')->references('id_pembelian')->on('purchases')->onDelete('cascade'); 
            $table->foreign('id_supplier')->references('id_supplier')->on('suppliers')->onDelete('cascade');  
            $table->foreign('id_produk')->references('id_produk')->on('items')->onDelete('cascade');  

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
