<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class TrackingScheduleTest extends TestCase
{
    public function test_cargotrack_auto_sync_is_not_scheduled(): void
    {
        // El schedule se registra cuando arranca Artisan.
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->all();

        $comandos = collect(app(Schedule::class)->events())->pluck('command')->implode("\n");

        $this->assertStringNotContainsString('tracking:sync-admin', $comandos);
        $this->assertStringNotContainsString('tracking:sync-cliente', $comandos);
    }
}
