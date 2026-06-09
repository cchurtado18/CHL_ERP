<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parametros_rentabilidad', function (Blueprint $table) {
            $table->unsignedBigInteger('servicio_id')->nullable()->after('costo_fijo_por_libra');
            $table->foreign('servicio_id')->references('id')->on('servicios')->nullOnDelete();
            $table->index(['servicio_id', 'vigente_desde']);
        });
    }

    public function down(): void
    {
        Schema::table('parametros_rentabilidad', function (Blueprint $table) {
            $table->dropForeign(['servicio_id']);
            $table->dropIndex(['servicio_id', 'vigente_desde']);
            $table->dropColumn('servicio_id');
        });
    }
};
