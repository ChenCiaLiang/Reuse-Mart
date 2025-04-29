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
        Schema::create('penukaran', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tanggalPenerimaan');
            $table->dateTime('tanggalPengajuan');
            $table->unsignedBigInteger('idMerchandise');
            $table->unsignedBigInteger('idPembeli');
            $table->foreign('idMerchandise')->references('idMerchandise')->on('merchandise');
            $table->foreign('idPembeli')->references('idPembeli')->on('pembeli');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penukaran');
    }
};