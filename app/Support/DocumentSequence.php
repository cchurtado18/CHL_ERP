<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DocumentSequence
{
    public function next(string $name): int
    {
        return (int) DB::transaction(function () use ($name) {
            $row = DB::table('document_sequences')->where('name', $name)->lockForUpdate()->first();
            if (! $row) {
                throw new \RuntimeException("Secuencia de documento no inicializada: {$name}");
            }
            $next = (int) $row->current_value + 1;
            DB::table('document_sequences')->where('name', $name)->update(['current_value' => $next]);

            return $next;
        });
    }

    /** Lee el próximo valor sin consumir la secuencia (solo previews). */
    public function peek(string $name): int
    {
        $row = DB::table('document_sequences')->where('name', $name)->first();
        if (! $row) {
            return 1;
        }

        return (int) $row->current_value + 1;
    }
}
