<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $voucher->folio }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; margin: 28px; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #15537c; }
        .muted { color: #64748b; }
        .box { border: 1px solid #cbd5e1; border-radius: 6px; padding: 14px; margin-top: 16px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 0; vertical-align: top; }
        .label { color: #64748b; width: 32%; }
        .amount { font-size: 20px; font-weight: bold; color: #15537c; }
        .footer { margin-top: 48px; font-size: 10px; color: #94a3b8; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; }
        .ingreso { background: #ecfdf5; color: #065f46; }
        .egreso { background: #fff1f2; color: #9f1239; }
    </style>
</head>
<body>
    <p class="muted">CH Logistics · Contabilidad</p>
    <h1>Voucher {{ $voucher->folio }}</h1>
    <p>
        <span class="badge {{ $voucher->esIngreso() ? 'ingreso' : 'egreso' }}">{{ $voucher->etiquetaTipo() }}</span>
        &nbsp; {{ $voucher->fecha?->format('d/m/Y') }}
    </p>

    <div class="box">
        <table>
            <tr>
                <td class="label">Tercero</td>
                <td>{{ $voucher->tercero_nombre }}</td>
            </tr>
            <tr>
                <td class="label">Concepto</td>
                <td>{{ $voucher->concepto }}</td>
            </tr>
            <tr>
                <td class="label">Método</td>
                <td>{{ ucfirst($voucher->metodo ?: '—') }}</td>
            </tr>
            @if($voucher->referencia)
            <tr>
                <td class="label">Referencia</td>
                <td>{{ $voucher->referencia }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Monto</td>
                <td class="amount">${{ number_format((float)$voucher->monto, 2) }} {{ $voucher->moneda }}</td>
            </tr>
        </table>
    </div>

    <p class="footer">Documento generado por el sistema. No reemplaza factura fiscal.</p>
</body>
</html>
