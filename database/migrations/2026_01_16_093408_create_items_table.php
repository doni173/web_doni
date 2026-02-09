<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            // Primary Key
            $table->char('id_produk', 5)->primary();
            
            // Product Information
            $table->string('nama_produk', 100);
            $table->date('tanggal_masuk')->nullable();
            
            // Foreign Keys
            $table->char('id_kategori', 5);
            $table->char('id_brand', 5);
            $table->char('id_supplier', 5);
            
            // Stock & Unit
            $table->string('satuan', 30);
            $table->unsignedInteger('stok')->default(0);
            
            // Pricing
            $table->unsignedInteger('modal')->default(0);
            $table->unsignedInteger('harga_jual')->default(0);
            $table->decimal('diskon', 5, 2)->default(0.00);
            $table->decimal('harga_setelah_diskon', 15, 2)->nullable();
            
            // FSN Analysis
            $table->string('FSN', 255)->default('NA')->nullable();
            $table->decimal('tor_value', 10, 2)->nullable();
            $table->dateTime('last_fsn_calculation')->nullable();
            
            // ✅ PERBAIKAN: Diskon Bertingkat dengan dokumentasi lebih jelas
            $table->integer('consecutive_n_months')->default(0)
                  ->comment('Jumlah bulan berturut-turut berstatus N untuk diskon bertingkat: 1 bulan=5%, 2 bulan=10%, 3+ bulan=15%');
            
            // Timestamps
            $table->timestamps();
            
            // ✅ PERBAIKAN: Tambahkan index untuk performa query
            $table->index(['FSN', 'tanggal_masuk'], 'idx_fsn_tanggal');
            $table->index('last_fsn_calculation', 'idx_last_calc');
            $table->index('consecutive_n_months', 'idx_consecutive');
            
            // Foreign Key Constraints
            $table->foreign('id_kategori')
                  ->references('id_kategori')
                  ->on('categories')
                  ->onDelete('cascade');

            $table->foreign('id_brand')
                  ->references('id_brand')
                  ->on('brands')
                  ->onDelete('cascade');

            $table->foreign('id_supplier')
                  ->references('id_supplier')
                  ->on('suppliers')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
}