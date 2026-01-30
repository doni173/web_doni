<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->char('id_detail_pembelian', 5)->primary();
            $table->char('id_pembelian',5); 
            $table->char('id_produk', 5);
            $table->integer('jumlah')->default(0);
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_pembelian')
                ->references('id_pembelian')
                ->on('purchase')
                ->onDelete('cascade');

            $table->foreign('id_produk')
                ->references('id_produk')
                ->on('items')
                ->onDelete('restrict');

            // Index untuk performa
            $table->index('id_pembelian');
            $table->index('id_produk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};