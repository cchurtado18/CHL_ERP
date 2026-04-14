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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 40)->unique();
            $table->string('nombre_completo');
            $table->string('telefono', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('campana', 160)->nullable();
            $table->string('origen', 100)->nullable();
            $table->enum('etapa', ['nuevo', 'contactado', 'interesado', 'negociacion', 'seguimiento', 'convertido', 'perdido'])->default('nuevo');
            $table->string('interes_servicio', 120)->nullable();
            $table->decimal('presupuesto_estimado', 12, 2)->nullable();
            $table->dateTime('proximo_contacto_at')->nullable();
            $table->dateTime('ultimo_contacto_at')->nullable();
            $table->enum('estado_recordatorio', ['pendiente', 'enviado', 'completado'])->default('pendiente');
            $table->enum('resultado', ['abierto', 'convertido', 'perdido'])->default('abierto');
            $table->dateTime('fecha_cierre')->nullable();
            $table->string('motivo_perdida', 180)->nullable();
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('etapa');
            $table->index('proximo_contacto_at');
            $table->index('resultado');
            $table->index('campana');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
