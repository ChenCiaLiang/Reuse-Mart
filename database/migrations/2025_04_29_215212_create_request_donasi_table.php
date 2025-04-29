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
        Schema::create('request_donasi', function (Blueprint $table) {
            $table->id('idRequest');
            $table->dateTime('tanggalRequest');
            $table->string('request', 150);
            $table->string('status', 50);
            $table->string('penerima', 50);
            $table->string('idOrganisasi', 10);
            $table->foreign('idOrganisasi')->references('idOrganisasi')->on('organisasi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_donasi');
    }
};