<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Una misma encomienda puede tener varias filas en facturacion si las anteriores están anuladas.
     * El índice único en encomienda_id impedía insertar la nueva factura; la regla unique en Laravel
     * ya limita a una factura activa (no anulada) por encomienda.
     */
    public function up(): void
    {
        Schema::table('facturacion', function (Blueprint $table) {
            $table->dropUnique(['encomienda_id']);
        });
    }

    public function down(): void
    {
        Schema::table('facturacion', function (Blueprint $table) {
            $table->unique('encomienda_id');
        });
    }
};
