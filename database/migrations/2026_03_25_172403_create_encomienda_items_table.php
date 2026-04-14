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
        Schema::create('encomienda_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encomienda_id')->constrained('encomiendas')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('tipo_item');
            $table->string('descripcion')->nullable();
            $table->unsignedInteger('cantidad')->default(1);
            $table->enum('metodo_cobro', ['peso', 'pie_cubico']);
            $table->decimal('peso_lb', 10, 2)->nullable();
            $table->decimal('largo_in', 10, 2)->nullable();
            $table->decimal('ancho_in', 10, 2)->nullable();
            $table->decimal('alto_in', 10, 2)->nullable();
            $table->decimal('pie_cubico', 10, 4)->nullable();
            $table->decimal('tarifa_manual', 10, 2);
            $table->decimal('monto_total_item', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encomienda_items');
    }
};
