<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conta_cxc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('factura_id')->unique();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->unsignedSmallInteger('dias_credito')->default(0);
            $table->decimal('monto_original', 14, 2)->default(0);
            $table->decimal('saldo_actual', 14, 2)->default(0);
            $table->enum('estado_cobro', ['al_dia', 'vencida', 'en_gestion', 'castigada', 'pagada'])->default('al_dia');
            $table->unsignedSmallInteger('dias_mora')->default(0);
            $table->timestamps();

            $table->index(['estado_cobro', 'fecha_vencimiento']);
            $table->foreign('factura_id')->references('id')->on('facturacion')->cascadeOnDelete();
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conta_cxc');
    }
};
