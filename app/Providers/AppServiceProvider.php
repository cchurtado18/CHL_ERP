<?php

namespace App\Providers;

use App\Contracts\TrackingProvider;
use App\Services\Tracking\CargoTrackProvider;
use App\Services\Tracking\NullTrackingProvider;
use App\Support\DocumentSequence;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DocumentSequence::class, fn () => new DocumentSequence);

        $this->app->singleton(TrackingProvider::class, function () {
            $driver = strtolower((string) config('services.tracking.provider', 'cargotrack'));

            return match ($driver) {
                'cargotrack' => new CargoTrackProvider,
                default => new NullTrackingProvider,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
