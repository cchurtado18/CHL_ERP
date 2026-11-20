<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargotrack_settings', function (Blueprint $table) {
            $table->id();
            $table->string('admin_username')->nullable();
            $table->text('admin_password')->nullable(); // encrypted cast
            $table->boolean('sync_enabled')->default(false);
            $table->boolean('fetch_documents')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->text('last_sync_message')->nullable();
            $table->timestamps();
        });

        Schema::create('cargotrack_agencias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique(); // CHLOGIX, SHIPIA
            $table->string('nombre')->nullable();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('cargotrack_inbound', function (Blueprint $table) {
            $table->id();
            $table->string('warehouse', 20); // guía 6 dígitos
            $table->string('tracking_codigo', 120)->nullable();
            $table->string('agencia_codigo', 50)->nullable()->index();
            $table->string('consignee_nombre')->nullable();
            $table->string('shipper')->nullable();
            $table->string('modalidad', 20)->nullable(); // aereo|maritimo
            $table->date('fecha_warehouse')->nullable();
            $table->unsignedInteger('piezas')->nullable();
            $table->decimal('peso_lb', 10, 2)->nullable();
            $table->decimal('volumen', 12, 4)->nullable();
            $table->decimal('valor', 12, 2)->nullable();
            $table->string('estado')->nullable();
            $table->string('status_raw')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('detail_url', 500)->nullable();
            $table->string('documento_path')->nullable();
            $table->string('documento_mime', 100)->nullable();
            $table->string('documento_url_origen', 500)->nullable();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('inventario_id')->nullable()->constrained('inventario')->nullOnDelete();
            $table->string('assign_status', 30)->default('pending'); // pending|assigned|unmapped|error
            $table->json('raw')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('warehouse');
            $table->index('tracking_codigo');
            $table->index('assign_status');
        });

        Schema::table('inventario', function (Blueprint $table) {
            $table->string('cargotrack_agencia', 50)->nullable()->after('tracking_codigo');
            $table->string('cargotrack_consignee')->nullable()->after('cargotrack_agencia');
            $table->string('cargotrack_shipper')->nullable()->after('cargotrack_consignee');
            $table->string('cargotrack_modalidad', 20)->nullable()->after('cargotrack_shipper');
            $table->unsignedInteger('piezas')->nullable()->after('cargotrack_modalidad');
            $table->decimal('volumen', 12, 4)->nullable()->after('piezas');
            $table->string('documento_path')->nullable()->after('volumen');
            $table->string('documento_mime', 100)->nullable()->after('documento_path');
            $table->timestamp('cargotrack_synced_at')->nullable()->after('documento_mime');
        });
    }

    public function down(): void
    {
        Schema::table('inventario', function (Blueprint $table) {
            $table->dropColumn([
                'cargotrack_agencia',
                'cargotrack_consignee',
                'cargotrack_shipper',
                'cargotrack_modalidad',
                'piezas',
                'volumen',
                'documento_path',
                'documento_mime',
                'cargotrack_synced_at',
            ]);
        });

        Schema::dropIfExists('cargotrack_inbound');
        Schema::dropIfExists('cargotrack_agencias');
        Schema::dropIfExists('cargotrack_settings');
    }
};
