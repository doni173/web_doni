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
    Schema::create('sales', function (Blueprint $table) {
        $table->char('id_penjualan', 5)->primary();
        $table->char('id_user', 5);
        $table->string('id_pelanggan');
        $table->decimal('total_belanja', 15, 2);
        $table->decimal('total_bayar', 15, 2);
        $table->decimal('jumlah_bayar', 15, 2);
        $table->decimal('kembalian', 15, 2);
        $table->date('tanggal_transaksi');
        $table->timestamps();

        $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        $table->foreign('id_pelanggan')->references('id_pelanggan')->on('customers')->onDelete('cascade');
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
