<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Atributos que pueden ser asignados masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol',
        'permisos',
        'cliente_id',
        'estado',
    ];

    /**
     * Atributos ocultos para arrays/JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversión de tipos de atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'estado' => 'boolean',
        'password' => 'hashed',
        'permisos' => 'array',
    ];

    public function username()
    {
        return 'nombre';
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function esCliente(): bool
    {
        return $this->rol === 'cliente';
    }

    /**
     * Lista de módulos permitidos (vacía si es admin: acceso total).
     *
     * @return list<string>
     */
    public function listaPermisos(): array
    {
        if ($this->esCliente()) {
            return [];
        }

        if ($this->esAdmin()) {
            return array_keys(config('permisos.modulos', []));
        }

        $permisos = $this->permisos;

        return is_array($permisos) ? array_values(array_unique($permisos)) : [];
    }

    public function tienePermiso(string $modulo): bool
    {
        if ($this->esCliente()) {
            return false;
        }

        if ($this->esAdmin()) {
            return true;
        }

        $permisos = $this->listaPermisos();

        if (in_array($modulo, $permisos, true)) {
            return true;
        }

        // Contabilidad completa incluye registrar cobros.
        if ($modulo === 'contabilidad.cobros' && in_array('contabilidad', $permisos, true)) {
            return true;
        }

        return false;
    }

    public function tieneAlgunPermiso(string ...$modulos): bool
    {
        foreach ($modulos as $modulo) {
            if ($this->tienePermiso($modulo)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Primera ruta a la que el usuario puede entrar según sus permisos.
     */
    public function homePath(): string
    {
        if ($this->esCliente()) {
            return '/portal';
        }

        $rutas = [
            'dashboard' => '/',
            'inventario' => '/inventario',
            'clientes' => '/clientes',
            'facturacion' => '/facturacion',
            'encomiendas' => '/encomiendas',
            'contabilidad' => '/contabilidad',
            'contabilidad.cobros' => '/contabilidad/cobros/crear',
            'contabilidad.reportes' => '/contabilidad/reporte-ejecutivo',
            'leads' => '/leads',
            'tracking' => '/tracking',
            'notificaciones' => '/notificaciones',
            'usuarios' => '/usuarios',
            'remitentes' => '/remitentes',
            'destinatarios' => '/destinatarios',
            'logs_inventario' => '/logs-inventario',
        ];

        foreach ($rutas as $modulo => $path) {
            if ($this->tienePermiso($modulo)) {
                return $path;
            }
        }

        return '/sin-acceso';
    }
}
