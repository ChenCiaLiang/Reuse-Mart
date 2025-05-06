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
        Schema::create('gambar_produk', function (Blueprint $table) {
            $table->id('idGambar');
            $table->string('gambar', 255);
            $table->string('idProduk', 10);
            $table->timestamps();
            
            $table->foreign('idProduk')
                  ->references('idProduk')
                  ->on('produk')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gambar_produk');
    }
};
