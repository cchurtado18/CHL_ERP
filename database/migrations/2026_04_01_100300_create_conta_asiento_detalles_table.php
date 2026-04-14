<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conta_asiento_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asiento_id')->constrained('conta_asientos')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('cuenta_id')->constrained('conta_cuentas')->restrictOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('tercero_id')->nullable();
            $table->string('tercero_tipo', 40)->nullable();
            $table->decimal('debito', 14, 2)->default(0);
            $table->decimal('credito', 14, 2)->default(0);
            $table->decimal('monto_origen', 14, 2)->nullable();
            $table->decimal('monto_funcional', 14, 2)->nullable();
            $table->text('glosa')->nullable();
            $table->timestamps();

            $table->index('cuenta_id');
            $table->index(['tercero_tipo', 'tercero_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conta_asiento_detalles');
    }
};
