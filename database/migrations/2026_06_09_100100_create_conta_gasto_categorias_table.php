<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conta_gasto_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('slug', 100)->unique();
            $table->unsignedBigInteger('cuenta_contable_id')->nullable();
            $table->string('icono', 50)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index('activa');
            $table->foreign('cuenta_contable_id')->references('id')->on('conta_cuentas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conta_gasto_categorias');
    }
};
