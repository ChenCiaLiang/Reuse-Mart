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
        Schema::create('transaksi_penjualan', function (Blueprint $table) {
            $table->id('idTransaksiPenjualan');
            $table->float('bonus')->default(0);
            $table->dateTime('tanggalLaku')->nullable();
            $table->dateTime('tanggalPesan');
            $table->dateTime('tanggalBatasLunas');
            $table->dateTime('tanggalLunas')->nullable();
            $table->dateTime('tanggalKirim')->nullable();
            $table->dateTime('tanggalAmbil')->nullable();
            $table->unsignedBigInteger('idPembeli');
            $table->foreign('idPembeli')
                ->references('idPembeli')->on('pembeli')
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
        Schema::dropIfExists('transaksi_penjualan');
    }
};
