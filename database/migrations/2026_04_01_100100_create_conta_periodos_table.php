<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conta_periodos', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes');
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->dateTime('fecha_cierre')->nullable();
            $table->unsignedBigInteger('cerrado_por')->nullable();
            $table->timestamps();

            $table->unique(['anio', 'mes']);
            $table->index('estado');
            $table->foreign('cerrado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conta_periodos');
    }
};
