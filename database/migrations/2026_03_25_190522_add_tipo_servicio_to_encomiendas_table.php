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
        Schema::table('encomiendas', function (Blueprint $table) {
            $table->string('tipo_servicio', 20)->default('maritimo')->after('estado_actual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encomiendas', function (Blueprint $table) {
            $table->dropColumn('tipo_servicio');
        });
    }
};
