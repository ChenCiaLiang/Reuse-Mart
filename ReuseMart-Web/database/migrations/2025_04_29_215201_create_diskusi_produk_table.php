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
        Schema::create('diskusi_produk', function (Blueprint $table) {
            $table->id('idDiskusi');
            $table->string('pesan');
            $table->dateTime('tanggalDiskusi');
            $table->unsignedBigInteger('idPegawai')->nullable();
            $table->unsignedBigInteger('idProduk');
            $table->unsignedBigInteger('idPembeli')->nullable();
            $table->foreign('idPegawai')
                ->references('idPegawai')->on('pegawai')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('idProduk')
                ->references('idProduk')->on('produk')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
        Schema::dropIfExists('diskusi_produk');
    }
};
