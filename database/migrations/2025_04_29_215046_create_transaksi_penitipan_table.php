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
        Schema::create('transaksi_penitipan', function (Blueprint $table) {
            $table->id('idTransaksiPenitipan');
            $table->dateTime('tanggalMasukPenitipan');
            $table->dateTime('tanggalAkhirPenitipan');
            $table->dateTime('batasAmbil');
            $table->string('statusPenitipan', 10);
            $table->boolean('statusPerpanjangan');
            $table->float('pendapatan');
            $table->unsignedBigInteger('idPenitip');
            $table->foreign('idPenitip')
                ->references('idPenitip')->on('penitip')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unsignedBigInteger('idPegawai');
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
        Schema::dropIfExists('transaksi_penitipan');
    }
};
