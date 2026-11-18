<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE inventario MODIFY peso_lb DECIMAL(12, 4) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE inventario ALTER COLUMN peso_lb TYPE DECIMAL(12, 4)');
        }
        // SQLite no limita la escala; se guarda el valor completo.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE inventario MODIFY peso_lb DECIMAL(8, 2) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE inventario ALTER COLUMN peso_lb TYPE DECIMAL(8, 2)');
        }
    }
};
