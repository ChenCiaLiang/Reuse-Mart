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
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Ubah tipe data kolom tokenable_id dari bigInteger ke string
            $table->string('tokenable_id', 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Kembalikan ke tipe data semula jika perlu rollback
            $table->unsignedBigInteger('tokenable_id')->change();
        });
    }
};
