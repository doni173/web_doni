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
    Schema::create('sale_details', function (Blueprint $table) {
        $table->char('id_detail_penjualan', 5)->primary();
        $table->char('id_penjualan', 5);
        $table->char('id_produk', 5);
        $table->char('id_service', 5);
        $table->integer('jumlah'); 
        $table->decimal('diskon', 15, 2)->default(0);
        $table->decimal('harga_setelah_diskon', 15, 2);
        $table->timestamps();

        $table->foreign('id_penjualan')->references('id_penjualan')->on('sales')->onDelete('cascade');
        $table->foreign('id_produk')->references('id_produk')->on('items')->onDelete('cascade');
        $table->foreign('id_service')->references('id_service')->on('services')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
