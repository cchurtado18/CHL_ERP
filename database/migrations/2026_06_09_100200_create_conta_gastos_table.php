<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conta_gastos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedBigInteger('categoria_id');
            $table->string('descripcion', 500);
            $table->decimal('monto', 14, 2);
            $table->enum('moneda', ['USD', 'NIO'])->default('USD');
            $table->decimal('tasa_cambio', 12, 4)->nullable();
            $table->unsignedBigInteger('cuenta_pago_id');
            $table->string('referencia', 120)->nullable();
            $table->unsignedBigInteger('asiento_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('fecha');
            $table->index('categoria_id');
            $table->foreign('categoria_id')->references('id')->on('conta_gasto_categorias')->restrictOnDelete();
            $table->foreign('cuenta_pago_id')->references('id')->on('conta_cuentas')->restrictOnDelete();
            $table->foreign('asiento_id')->references('id')->on('conta_asientos')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conta_gastos');
    }
};
