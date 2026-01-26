<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->char('id_penjualan', 5)->primary(); // Primary key dengan tipe CHAR(5)
            $table->date('tgl_penjualan'); // Tanggal penjualan
            $table->char('id_produk', 5); // Foreign Key untuk Id Produk
            $table->char('id_service', 5); // Foreign Key untuk Id Produk
            $table->char('id_pelanggan', 5); // Foreign Key untuk Id Pelanggan
            $table->char('id_user', 5); // Foreign Key untuk Id User
            $table->timestamps(); // Menambahkan created_at dan updated_at

            // Definisi foreign keys
            $table->foreign('id_produk')->references('id_produk')->on('items')->onDelete('cascade'); // Mengarah ke tabel 'items'
            $table->foreign('id_service')->references('id_service')->on('services')->onDelete('cascade'); // Mengarah ke tabel 'items'
            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('customers')->onDelete('cascade'); // Mengarah ke tabel 'customers'
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade'); // Mengarah ke tabel 'users'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
