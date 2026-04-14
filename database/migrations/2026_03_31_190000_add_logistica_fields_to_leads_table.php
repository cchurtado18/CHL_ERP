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
            $table->string('direccion_cliente', 255)->nullable()->after('email');
            $table->string('estado_usa_origen', 120)->nullable()->after('origen');
            $table->string('departamento_destino', 120)->nullable()->after('estado_usa_origen');
            $table->string('municipio_destino', 120)->nullable()->after('departamento_destino');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'direccion_cliente',
                'estado_usa_origen',
                'departamento_destino',
                'municipio_destino',
            ]);
        });
    }
};
