<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paquete_estado_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('inventario')->cascadeOnDelete();
            $table->string('estado');
            $table->string('estado_origen')->default('local'); // local | primetrack
            $table->string('ubicacion')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('evento_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['inventario_id', 'evento_at']);
            $table->index(['inventario_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paquete_estado_eventos');
    }
};
