<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conta_asientos', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 40)->unique();
            $table->date('fecha');
            $table->unsignedSmallInteger('periodo_anio');
            $table->unsignedTinyInteger('periodo_mes');
            $table->string('referencia_tipo', 60)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->text('descripcion')->nullable();
            $table->enum('moneda', ['USD', 'NIO'])->default('USD');
            $table->decimal('tasa_cambio', 12, 4)->nullable();
            $table->decimal('total_debito', 14, 2)->default(0);
            $table->decimal('total_credito', 14, 2)->default(0);
            $table->enum('estado', ['borrador', 'contabilizado', 'anulado'])->default('contabilizado');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();

            $table->index(['periodo_anio', 'periodo_mes']);
            $table->index(['referencia_tipo', 'referencia_id']);
            $table->index('estado');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conta_asientos');
    }
};
