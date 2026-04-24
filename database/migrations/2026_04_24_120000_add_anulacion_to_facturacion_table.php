<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturacion', function (Blueprint $table) {
            $table->boolean('anulada')->default(false);
            $table->timestamp('anulada_at')->nullable();
            $table->unsignedBigInteger('anulada_por')->nullable();
            $table->text('anulacion_motivo')->nullable();

            $table->foreign('anulada_por')->references('id')->on('users')->nullOnDelete();
            $table->index('anulada');
        });
    }

    public function down(): void
    {
        Schema::table('facturacion', function (Blueprint $table) {
            $table->dropForeign(['anulada_por']);
            $table->dropIndex(['anulada']);
            $table->dropColumn(['anulada', 'anulada_at', 'anulada_por', 'anulacion_motivo']);
        });
    }
};
