<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trabajos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('asignado_a')->constrained('users')->cascadeOnDelete();
            $table->foreignId('asignado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_programada');
            $table->string('estado', 40)->default('asignado');
            $table->string('prioridad', 20)->default('promedio');
            $table->timestamp('visto_at')->nullable();
            $table->timestamp('finalizado_at')->nullable();
            $table->text('nota_estado')->nullable();
            $table->timestamps();

            $table->index(['asignado_a', 'fecha_programada']);
            $table->index(['estado', 'fecha_programada']);
            $table->index('cliente_id');
            $table->index('prioridad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trabajos');
    }
};
