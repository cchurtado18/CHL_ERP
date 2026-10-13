<?php

namespace App\Services\Tracking;

use App\Contracts\TrackingProvider;

class NullTrackingProvider implements TrackingProvider
{
    public function enabled(): bool
    {
        return false;
    }

    public function driver(): string
    {
        return 'none';
    }

    public function lastError(): ?string
    {
        return null;
    }

    public function lookup(string $code): ?array
    {
        return null;
    }

    public function listPackagesForCredentials(string $username, string $password): array
    {
        return [];
    }
}
