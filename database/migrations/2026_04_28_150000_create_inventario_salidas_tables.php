<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_salidas', function (Blueprint $table) {
            $table->id();
            $table->text('descripcion');
            $table->string('sucursal_origen', 120)->nullable();
            $table->string('sucursal_destino', 120)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('inventario_salida_paquete', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_salida_id')->constrained('inventario_salidas')->cascadeOnDelete();
            $table->unsignedBigInteger('inventario_id');
            $table->foreign('inventario_id')->references('id')->on('inventario')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['inventario_salida_id', 'inventario_id'], 'salida_paquete_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_salida_paquete');
        Schema::dropIfExists('inventario_salidas');
    }
};
