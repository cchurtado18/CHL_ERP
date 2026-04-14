<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('encomiendas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('remitente_id')->constrained('remitentes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('destinatario_id')->constrained('destinatarios')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('estado_actual')->default('registrada');
            $table->unsignedInteger('cantidad_bultos')->default(1);
            $table->decimal('valor_declarado', 10, 2)->nullable();
            $table->text('descripcion_general')->nullable();
            $table->text('observaciones')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encomiendas');
    }
};
