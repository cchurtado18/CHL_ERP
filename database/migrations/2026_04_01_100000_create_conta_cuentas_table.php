<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conta_cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 180);
            $table->enum('tipo', ['activo', 'pasivo', 'patrimonio', 'ingreso', 'gasto', 'costo']);
            $table->string('subtipo', 100)->nullable();
            $table->unsignedBigInteger('cuenta_padre_id')->nullable();
            $table->boolean('acepta_movimiento')->default(true);
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index(['tipo', 'activa']);
            $table->foreign('cuenta_padre_id')->references('id')->on('conta_cuentas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conta_cuentas');
    }
};
