<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('conta_cuentas')) {
            return;
        }

        $exists = DB::table('conta_cuentas')->where('codigo', '5.2.01')->exists();
        if ($exists) {
            return;
        }

        $now = now();
        DB::table('conta_cuentas')->insert([
            'codigo' => '5.2.01',
            'nombre' => 'Costo de flete de proveedores',
            'tipo' => 'costo',
            'subtipo' => 'flete',
            'acepta_movimiento' => 1,
            'activa' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('conta_cuentas')) {
            return;
        }

        DB::table('conta_cuentas')->where('codigo', '5.2.01')->delete();
    }
};
