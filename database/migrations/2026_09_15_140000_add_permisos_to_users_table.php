<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('permisos')->nullable()->after('rol');
        });

        $defaults = config('permisos.defaults_por_rol', []);

        foreach (DB::table('users')->select('id', 'rol')->get() as $user) {
            if ($user->rol === 'admin') {
                // Admin = acceso total; no necesita lista explícita.
                continue;
            }

            $permisos = $defaults[$user->rol] ?? [];
            DB::table('users')->where('id', $user->id)->update([
                'permisos' => json_encode(array_values($permisos)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permisos');
        });
    }
};
