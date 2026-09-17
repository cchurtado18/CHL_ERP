<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->string('name', 64)->primary();
            $table->unsignedBigInteger('current_value')->default(0);
        });

        Schema::table('facturacion', function (Blueprint $table) {
            $table->unsignedBigInteger('folio')->nullable()->after('id');
        });

        $ids = DB::table('facturacion')->orderBy('id')->pluck('id');
        $n = 1;
        foreach ($ids as $id) {
            DB::table('facturacion')->where('id', $id)->update(['folio' => $n]);
            $n++;
        }

        Schema::table('facturacion', function (Blueprint $table) {
            $table->unique('folio');
        });

        $maxFolio = (int) DB::table('facturacion')->max('folio');
        DB::table('document_sequences')->insert([
            'name' => 'facturacion',
            'current_value' => $maxFolio,
        ]);
    }

    public function down(): void
    {
        Schema::table('facturacion', function (Blueprint $table) {
            $table->dropUnique(['folio']);
            $table->dropColumn('folio');
        });
        Schema::dropIfExists('document_sequences');
    }
};
