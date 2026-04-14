<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('encomienda_items', function (Blueprint $table) {
            $table->string('foto_path', 512)->nullable()->after('monto_total_item');
        });
    }

    public function down(): void
    {
        Schema::table('encomienda_items', function (Blueprint $table) {
            $table->dropColumn('foto_path');
        });
    }
};
