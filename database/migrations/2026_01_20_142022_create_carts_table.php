<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_xxxxxx_create_carts_table.php
public function up()
{
    Schema::create('carts', function (Blueprint $table) {
        $table->id();
        $table->char('id_user', 5); 
        $table->date('tanggal_transaksi'); // Tanggal transaksi
        $table->string('nama_pelanggan'); // Nama pelanggan
        $table->decimal('total_belanja', 10, 2)->default(0); // Total belanja
        $table->decimal('jumlah_bayar', 10, 2)->default(0); // Jumlah bayar
        $table->decimal('kembalian', 10, 2)->default(0); // Kembalian
        $table->timestamps();

        // Menambahkan foreign key untuk mengaitkan dengan pengguna
        $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
