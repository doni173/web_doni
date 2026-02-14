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
        Schema::create('sale_details', function (Blueprint $table) {
            $table->char('id_detail_penjualan', 5)->primary();
            $table->char('id_penjualan', 5);

            // ✅ Nullable karena transaksi bisa produk ATAU service, tidak keduanya
            $table->char('id_produk', 5)->nullable();
            $table->char('id_service', 5)->nullable();

            $table->integer('jumlah');
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('harga_setelah_diskon', 15, 2);
            $table->timestamps();

            $table->foreign('id_penjualan')
                  ->references('id_penjualan')
                  ->on('sales')
                  ->onDelete('cascade');

            // ✅ Foreign key nullable — gunakan nullOnDelete()
            $table->foreign('id_produk')
                  ->references('id_produk')
                  ->on('items')
                  ->nullOnDelete();

            $table->foreign('id_service')
                  ->references('id_service')
                  ->on('services')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_details');
    }
};