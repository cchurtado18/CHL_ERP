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
     * Códigos a enviar a CargoTrack: el escrito, en mayúsculas, y TBA de 15 si pegaron dígitos de más.
     *
     * @return list<string>
     */
    public static function lookupVariants(string $code): array
    {
        $raw = trim($code);
        $norm = self::normalize($raw);
        $out = array_values(array_unique(array_filter([$raw, $norm])));

        // Amazon TBA = TBA + 12 dígitos. Un 0 extra al pegar hace que CargoTrack responda NOT FOUND.
        if (preg_match('/^(TBA\d{12})\d+$/i', $norm, $m) && ! in_array($m[1], $out, true)) {
            $out[] = $m[1];
        }

        return $out;
    }

    public static function isNumericGuia(string $code): bool
    {
        return (bool) preg_match('/^\d{5,9}$/', self::normalize($code));
    }
}
