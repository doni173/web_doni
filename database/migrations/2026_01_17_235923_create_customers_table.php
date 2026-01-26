<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->char('id_pelanggan', 5)->primary(); // Primary Key (CHAR 5)
            $table->string('nama_pelanggan', 50); 
            $table->unsignedBigInteger('no_hp'); // Gunakan unsignedBigInteger untuk nomor telepon dalam format integer
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};


