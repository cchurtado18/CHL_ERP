<?php

namespace App\Contracts;

use App\Models\Cliente;

interface TrackingProvider
{
    public function enabled(): bool;

    public function driver(): string;

    public function lastError(): ?string;

    /**
     * Consulta pública por código de tracking/guía.
     * Retorna null si no hay datos o el driver está apagado.
     *
     * wrh = número de guía / WRH (típicamente 6 dígitos).
     * tracking_number = código de tracking (ej. TBA…), no la guía.
     *
     * @return array{
     *   tracking_number: string,
     *   status: string,
     *   status_raw: string|null,
     *   wrh: string|null,
     *   weight: float|null,
     *   description: string|null,
     *   pickup_date: string|null,
     *   delivery_date: string|null,
     *   events: list<array{date?:string,time?:string,status:string,description?:string,evento_at?:string}>
     * }|null
     */
    public function lookup(string $code): ?array;

    /**
     * Login con credenciales de una subagencia y listar paquetes visibles en su cuenta.
     *
     * @return list<array{
     *   tracking_number: string,
     *   status?: string,
     *   status_raw?: string|null,
     *   wrh?: string|null,
     *   weight?: float|null,
     *   description?: string|null,
     *   pickup_date?: string|null,
     *   delivery_date?: string|null,
     *   events?: list<array<string,mixed>>
     * }>
     */
    public function listPackagesForCredentials(string $username, string $password): array;
}
