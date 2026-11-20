<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('cargotrack_username')->nullable()->after('fecha_registro');
            $table->text('cargotrack_password')->nullable()->after('cargotrack_username');
            $table->boolean('cargotrack_sync_enabled')->default(false)->after('cargotrack_password');
            $table->timestamp('cargotrack_last_sync_at')->nullable()->after('cargotrack_sync_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'cargotrack_username',
                'cargotrack_password',
                'cargotrack_sync_enabled',
                'cargotrack_last_sync_at',
            ]);
        });
    }
};
