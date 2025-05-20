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
            $table->id('idPenukaran');
            $table->dateTime('tanggalPenerimaan');
            $table->dateTime('tanggalPengajuan');
            $table->unsignedBigInteger('idMerchandise');
            $table->unsignedBigInteger('idPembeli');
            $table->foreign('idMerchandise')
                ->references('idMerchandise')->on('merchandise')
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
        Schema::dropIfExists('penukaran');
    }
};
