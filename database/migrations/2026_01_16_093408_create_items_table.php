<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->char('id_produk', 5)->primary();
            $table->string('nama_produk', 50);
            $table->char('id_kategori', 5);
            $table->char('id_brand', 5);
            $table->string('satuan', 30);
            $table->unsignedInteger('modal');
            $table->unsignedInteger('harga_jual');
            $table->unsignedInteger('stok');
            $table->enum('FSN', ['F', 'S', 'N']);
            $table->decimal('diskon', 5, 2)->default(0);
            $table->decimal('harga_setelah_diskon', 15, 2)->nullable(); // Perbaiki tipe data dan hapus AUTO_INCREMENT
            $table->foreign('id_kategori')->references('id_kategori')->on('categories')->onDelete('cascade');
            $table->foreign('id_brand')->references('id_brand')->on('brands')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
}
