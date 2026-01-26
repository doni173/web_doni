<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Menjalankan migrasi untuk membuat tabel.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->char('id_kategori', 5)->primary(); // Kolom Id_Kategori sebagai primary key
            $table->string('kategori', 50); // Kolom Kategori dengan panjang 50 karakter
            $table->timestamps(); // Menambahkan kolom created_at dan updated_at
        });
    }

    /**
     * Membatalkan migrasi (drop tabel).
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories'); // Menghapus tabel categories jika ada
    }
}
