<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Menjalankan migrasi untuk membuat tabel services.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->char('id_service', 5)->primary(); // Primary Key (CHAR 5)
            $table->string('service', 50);            // Nama service
            $table->unsignedInteger('harga_jual');
            $table->decimal('diskon', 5, 2)->default(0);
            $table->decimal('harga_setelah_diskon', 15, 2)->nullable();                 // Harga service (integer)
            $table->timestamps();                     // created_at & updated_at
        });
    }

    /**
     * Membalikkan perubahan migrasi.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
}
