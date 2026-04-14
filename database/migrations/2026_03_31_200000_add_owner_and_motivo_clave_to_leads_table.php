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
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->nullable()->after('updated_by');
            $table->string('motivo_perdida_clave', 80)->nullable()->after('motivo_perdida');

            $table->index('owner_id');
            $table->index('motivo_perdida_clave');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropIndex(['owner_id']);
            $table->dropIndex(['motivo_perdida_clave']);
            $table->dropColumn(['owner_id', 'motivo_perdida_clave']);
        });
    }
};
