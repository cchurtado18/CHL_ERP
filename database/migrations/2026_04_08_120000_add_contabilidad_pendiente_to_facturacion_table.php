<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturacion', function (Blueprint $table) {
            $table->boolean('contabilidad_pendiente')->default(false)->after('estado_pago');
        });
    }

    public function down(): void
    {
        Schema::table('facturacion', function (Blueprint $table) {
            $table->dropColumn('contabilidad_pendiente');
        });
    }
};
