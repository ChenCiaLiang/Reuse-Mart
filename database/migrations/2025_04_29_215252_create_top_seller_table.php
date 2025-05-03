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
        Schema::create('top_seller', function (Blueprint $table) {
            $table->string('idTopSeller', 10)->primary();
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');
            $table->string('idPenitip', 10);
            $table->foreign('idPenitip')->references('idPenitip')->on('penitip');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('top_seller');
    }
};