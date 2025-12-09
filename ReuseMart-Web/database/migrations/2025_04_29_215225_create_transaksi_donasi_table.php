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
            $table->id('idTransaksiDonasi');
            $table->dateTime('tanggalPemberian');
            $table->string('namaPenerima', 50);
            $table->unsignedBigInteger('idRequest');
            $table->unsignedBigInteger('idProduk');
            $table->foreign('idRequest')
                ->references('idRequest')->on('request_donasi')
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
        Schema::dropIfExists('transaksi_donasi');
    }
};
