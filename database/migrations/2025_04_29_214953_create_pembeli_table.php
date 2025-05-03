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
        Schema::create('pembeli', function (Blueprint $table) {
            $table->id('idPembeli');
            $table->string('nama', 50);
            $table->string('email', 50)->unique();
            $table->string('password');
            $table->string('foto_profile')->default('');
            $table->integer('poin')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembeli');
    }
};
