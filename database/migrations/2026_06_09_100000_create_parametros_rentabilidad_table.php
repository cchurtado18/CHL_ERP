<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametros_rentabilidad', function (Blueprint $table) {
            $table->id();
            $table->decimal('costo_fijo_por_libra', 10, 4)->default(0);
            $table->string('moneda', 3)->default('USD');
            $table->date('vigente_desde');
            $table->text('nota')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('vigente_desde');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametros_rentabilidad');
    }
};
