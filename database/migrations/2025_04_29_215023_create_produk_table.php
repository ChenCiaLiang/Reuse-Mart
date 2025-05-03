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
        Schema::create('produk', function (Blueprint $table) {
            $table->string('idProduk', 10)->primary();
            $table->string('gambar');
            $table->dateTime('tanggalGaransi')->nullable();
            $table->float('harga');
            $table->string('status', 50);
            $table->float('berat');
            $table->float('hargaJual');
            $table->string('deskripsi', 100);
            $table->float('ratingProduk')->default(0);
            $table->unsignedBigInteger('idKategori');
            $table->string('idPegawai', 10);
            $table->foreign('idKategori')->references('idKategori')->on('kategori_produk');
            $table->foreign('idPegawai')->references('idPegawai')->on('pegawai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
