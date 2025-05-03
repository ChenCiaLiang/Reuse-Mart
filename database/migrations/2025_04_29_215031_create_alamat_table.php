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
        Schema::create('alamat', function (Blueprint $table) {
            $table->id('idAlamat');
            $table->string('alamatLengkap');
            $table->string('jenis', 25);
            $table->boolean('statusDefault');
            $table->unsignedBigInteger('idPembeli');
            $table->foreign('idPembeli')->references('idPembeli')->on('pembeli');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamat');
    }
};
