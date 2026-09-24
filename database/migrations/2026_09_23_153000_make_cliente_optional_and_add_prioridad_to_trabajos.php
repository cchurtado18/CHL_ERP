<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('trabajos', 'prioridad')) {
            Schema::table('trabajos', function (Blueprint $table) {
                $table->string('prioridad', 20)->default('promedio');
            });
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // Recrear tabla para hacer cliente_id nullable en SQLite
            Schema::create('trabajos_tmp', function (Blueprint $table) {
                $table->id();
                $table->string('titulo');
                $table->text('descripcion')->nullable();
                $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
                $table->foreignId('asignado_a')->constrained('users')->cascadeOnDelete();
                $table->foreignId('asignado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('fecha_programada');
                $table->string('estado', 40)->default('asignado');
                $table->string('prioridad', 20)->default('promedio');
                $table->timestamp('visto_at')->nullable();
                $table->timestamp('finalizado_at')->nullable();
                $table->text('nota_estado')->nullable();
                $table->timestamps();
                $table->index(['asignado_a', 'fecha_programada']);
                $table->index(['estado', 'fecha_programada']);
                $table->index('cliente_id');
            });

            $cols = 'id, titulo, descripcion, cliente_id, asignado_a, asignado_por, fecha_programada, estado, prioridad, visto_at, finalizado_at, nota_estado, created_at, updated_at';
            DB::statement("INSERT INTO trabajos_tmp ($cols) SELECT $cols FROM trabajos");
            Schema::drop('trabajos');
            Schema::rename('trabajos_tmp', 'trabajos');

            return;
        }

        Schema::table('trabajos', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
        });
        DB::statement('ALTER TABLE trabajos MODIFY cliente_id BIGINT UNSIGNED NULL');
        Schema::table('trabajos', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('trabajos', 'prioridad')) {
            Schema::table('trabajos', function (Blueprint $table) {
                $table->dropColumn('prioridad');
            });
        }
    }
};
