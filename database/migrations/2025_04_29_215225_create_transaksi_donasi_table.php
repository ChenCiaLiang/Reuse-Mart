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
        Schema::create('transaksi_donasi', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tanggalPemberian');
            $table->string('namaPenerima', 50);
            $table->unsignedBigInteger('idRequest');
            $table->string('idProduk', 10);
            $table->foreign('idRequest')->references('idRequest')->on('request_donasi');
            $table->foreign('idProduk')->references('idProduk')->on('produk');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_donasi');
    }
};