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
        Schema::create('purchases', function (Blueprint $table) {
            $table->char('id_pembelian', 5)->primary();
            $table->date('tgl_pembelian');
            $table->char('id_supplier', 5);
            $table->char('id_user', 5);
            $table->decimal('total_pembelian', 15, 2)->default(0);
    
            $table->timestamps();

             $table->foreign('id_supplier')->references('id_supplier')->on('suppliers')->onDelete('cascade');
             $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
             $table->foreign('id_produk')->references('id_produk')->on('items')->onDelete('cascade');                
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};