<?php

namespace App\Support;

class TrackingCode
{
    public static function normalize(?string $code): string
    {
        $code = strtoupper(trim((string) $code));
        $code = str_replace(["\u{00A0}", "\u{200B}", "\u{200C}", "\u{200D}", "\u{FEFF}"], '', $code);
        $code = preg_replace('/\s+/', '', $code) ?? '';
        $code = preg_replace('/^(WRH|NO)[:\-]*/', '', $code) ?? '';

        return preg_replace('/[^A-Z0-9]/', '', $code) ?? '';
    }

    /**
     * Variantes para buscar en inventario (match exacto primero).
     *
     * @return list<string>
     */
    public static function variants(string $code): array
    {
        $raw = trim($code);
        $stripped = trim((string) preg_replace('/^(WRH|NO)[\s:\-]*/i', '', $raw));
        $norm = self::normalize($raw);

        return array_values(array_unique(array_filter([
            $raw,
            strtoupper($raw),
            $stripped,
            strtoupper($stripped),
            $norm,
        ], fn ($value) => is_string($value) && $value !== '')));
    }

    /**
     * Pocas variantes para no martillar el proveedor externo.
     *
     * @return list<string>
     */
    public static function lookupVariants(string $code): array
    {
        return array_slice(self::variants($code), 0, 3);
    }

    public static function isNumericGuia(string $code): bool
    {
        return (bool) preg_match('/^\d{5,9}$/', self::normalize($code));
    }
}
