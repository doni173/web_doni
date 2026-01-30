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
        Schema::create('purchase', function (Blueprint $table) {
            $table->char('id_pembelian', 5)->primary();
            $table->date('tanggal_pembelian');
            $table->string('nama_supplier', 255);
            $table->string('nomor_invoice', 100)->unique();
            $table->decimal('total_pembelian', 15, 2)->default(0);
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index untuk performa
            $table->index('tanggal_pembelian');
            $table->index('nama_supplier');
            $table->index('nomor_invoice');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase');
    }
};