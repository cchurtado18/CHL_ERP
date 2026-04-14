<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturacion', function (Blueprint $table) {
            $table->string('entrega_nombre', 150)->nullable()->after('delivery');
            $table->string('entrega_cedula', 80)->nullable()->after('entrega_nombre');
            $table->string('entrega_telefono', 80)->nullable()->after('entrega_cedula');
        });
    }

    public function down(): void
    {
        Schema::table('facturacion', function (Blueprint $table) {
            $table->dropColumn(['entrega_nombre', 'entrega_cedula', 'entrega_telefono']);
        });
    }
};
