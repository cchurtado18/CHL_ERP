<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturacion', function (Blueprint $table) {
            $table->foreignId('encomienda_id')->nullable()->after('cliente_id')->constrained('encomiendas')->nullOnDelete();
        });

        Schema::table('facturacion', function (Blueprint $table) {
            $table->unique('encomienda_id');
        });
    }

    public function down(): void
    {
        Schema::table('facturacion', function (Blueprint $table) {
            $table->dropUnique(['encomienda_id']);
        });

        Schema::table('facturacion', function (Blueprint $table) {
            $table->dropConstrainedForeignId('encomienda_id');
        });
    }
};
