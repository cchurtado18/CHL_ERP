<?php

namespace App\Providers;

use App\Contracts\TrackingProvider;
use App\Services\Tracking\CargoTrackProvider;
use App\Services\Tracking\NullTrackingProvider;
use App\Support\DocumentSequence;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        // Login: 5 intentos por minuto por correo+IP y 20 por IP.
        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by($email.'|'.$request->ip()),
                Limit::perMinute(20)->by('ip:'.$request->ip()),
            ];
        });

        // Rastreo público: cada consulta llama a servicios externos.
        RateLimiter::for('rastreo', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));
    }
}
