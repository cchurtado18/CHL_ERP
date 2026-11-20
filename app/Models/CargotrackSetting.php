<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargotrackSetting extends Model
{
    protected $table = 'cargotrack_settings';

    protected $fillable = [
        'admin_username',
        'admin_password',
        'sync_enabled',
        'fetch_documents',
        'last_sync_at',
        'last_sync_message',
    ];

    protected $casts = [
        'admin_password' => 'encrypted',
        'sync_enabled' => 'boolean',
        'fetch_documents' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    protected $hidden = [
        'admin_password',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function hasCredentials(): bool
    {
        return filled($this->admin_username) && filled($this->admin_password);
    }
}
