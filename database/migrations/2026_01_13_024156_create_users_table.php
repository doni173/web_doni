<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Menjalankan migrasi untuk membuat tabel users.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
           
            $table->char('id_user', 5)->primary(); 
            $table->string('nama_user', 50); 
            $table->string('username', 50); 
            $table->string('password', 60);
            $table->enum('role', ['admin','kasir']); 

            $table->timestamps(); 
        });
    }

    /**
     * Membatalkan migrasi.
     *
     * @return void
     */
    public function down()
    {
        // Menghapus tabel users jika migrasi dibatalkan
        Schema::dropIfExists('users');
    }
}
