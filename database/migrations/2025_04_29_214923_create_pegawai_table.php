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
        Schema::create('pegawai', function (Blueprint $table) {
            $table->string('idPegawai', 10)->primary();
            $table->string('nama', 50);
            $table->string('username', 10)->unique();
            $table->string('password');
            $table->string('foto_profile')->default('');
            $table->unsignedBigInteger('idJabatan');
            $table->foreign('idJabatan')->references('idJabatan')->on('jabatan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
