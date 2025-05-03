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
            $table->unsignedBigInteger('idTransaksi')->primary();
            $table->float('komisiPenitip');
            $table->float('komisiHunter');
            $table->float('komisiReuse');
            $table->string('idPegawai', 10);
            $table->string('idPenitip', 10);
            $table->foreign('idTransaksi')->references('idTransaksi')->on('transaksi_penjualan');
            $table->foreign('idPegawai')->references('idPegawai')->on('pegawai');
            $table->foreign('idPenitip')->references('idPenitip')->on('penitip');
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