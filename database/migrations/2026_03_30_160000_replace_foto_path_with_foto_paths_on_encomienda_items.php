<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('encomienda_items', function (Blueprint $table) {
            $table->json('foto_paths')->nullable()->after('monto_total_item');
        });

        $rows = DB::table('encomienda_items')->whereNotNull('foto_path')->get(['id', 'foto_path']);
        foreach ($rows as $row) {
            if ($row->foto_path === '') {
                continue;
            }
            DB::table('encomienda_items')->where('id', $row->id)->update([
                'foto_paths' => json_encode([$row->foto_path]),
            ]);
        }

        Schema::table('encomienda_items', function (Blueprint $table) {
            $table->dropColumn('foto_path');
        });
    }

    public function down(): void
    {
        Schema::table('encomienda_items', function (Blueprint $table) {
            $table->string('foto_path', 512)->nullable()->after('monto_total_item');
        });

        $rows = DB::table('encomienda_items')->whereNotNull('foto_paths')->get(['id', 'foto_paths']);
        foreach ($rows as $row) {
            $paths = json_decode($row->foto_paths, true);
            $first = is_array($paths) && count($paths) > 0 ? $paths[0] : null;
            DB::table('encomienda_items')->where('id', $row->id)->update(['foto_path' => $first]);
        }

        Schema::table('encomienda_items', function (Blueprint $table) {
            $table->dropColumn('foto_paths');
        });
    }
};
