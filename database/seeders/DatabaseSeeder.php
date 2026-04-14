<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear un usuario admin
        User::factory()->create([
            'nombre' => 'Test User', // ✅ este es el campo correcto según tu migración
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'rol' => 'admin',
            'estado' => 1,
        ]);

        // Ejecutar seeders
        $this->call([
            ServicioSeeder::class,
            NotificacionSeeder::class,
            TrackingSeeder::class,
            ContaCuentaSeeder::class,
        ]);
    }
}
