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
        Schema::create('penitip', function (Blueprint $table) {
            $table->id('idPenitip');
            $table->string('nama', 50);
            $table->string('email', 50)->unique();
            $table->string('password');
            $table->string('alamat', 200);
            $table->string('nik', 16);
            $table->string('fotoKTP')->default('');
            $table->integer('poin')->default(0);
            $table->float('bonus')->default(0);
            $table->float('komisi')->default(0);
            $table->float('saldo')->default(0);
            $table->float('rating')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penitip');
    }
};
