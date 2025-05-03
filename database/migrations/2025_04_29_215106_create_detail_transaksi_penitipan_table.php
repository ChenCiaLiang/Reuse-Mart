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
        Schema::create('detail_transaksi_penitipan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idTransaksiPenitipan');
            $table->string('idProduk', 10);
            $table->foreign('idTransaksiPenitipan')->references('idTransaksiPenitipan')->on('transaksi_penitipan');
            $table->foreign('idProduk')->references('idProduk')->on('produk');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi_penitipan');
    }
};