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
        Schema::create('detail_transaksi_penjualan', function (Blueprint $table) {
            $table->id('idDetailTransaksiPenjualan');
            $table->unsignedBigInteger('idTransaksiPenjualan');
            $table->unsignedBigInteger('idProduk');
            $table->foreign('idTransaksiPenjualan')
                ->references('idTransaksiPenjualan')->on('transaksi_penjualan')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('idProduk')
                ->references('idProduk')->on('produk')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi_penjualan');
    }
};
