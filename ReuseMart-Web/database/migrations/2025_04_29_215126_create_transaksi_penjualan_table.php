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
            $table->float('status')->nullable();
            $table->dateTime('tanggalLaku')->nullable();
            $table->dateTime('tanggalPesan');
            $table->dateTime('tanggalBatasLunas');
            $table->dateTime('tanggalLunas')->nullable();
            $table->dateTime('tanggalBatasAmbil')->nullable();
            $table->dateTime('tanggalKirim')->nullable();
            $table->dateTime('tanggalAmbil')->nullable();
            $table->unsignedBigInteger('idPembeli');
            $table->unsignedBigInteger('idPegawai')->nullable(); //KURIR
            $table->foreign('idPembeli')
                ->references('idPembeli')->on('pembeli')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('idPegawai')
                ->references('idPegawai')->on('pegawai')
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
