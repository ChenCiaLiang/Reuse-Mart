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
        Schema::create('komisi', function (Blueprint $table) {
            $table->unsignedBigInteger('idTransaksiPenjualan');
            $table->float('komisiPenitip');
            $table->float('komisiHunter');
            $table->float('komisiReuse');
            $table->unsignedBigInteger('idPegawai');
            $table->unsignedBigInteger('idPenitip');
            $table->foreign('idTransaksiPenjualan')
                ->references('idTransaksiPenjualan')->on('transaksi_penjualan')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('idPegawai')
                ->references('idPegawai')->on('pegawai')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('idPenitip')
                ->references('idPenitip')->on('penitip')
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
        Schema::dropIfExists('komisi');
    }
};
