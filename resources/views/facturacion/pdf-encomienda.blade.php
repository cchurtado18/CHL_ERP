@php
    $enc = isset($factura->encomienda) && $factura->encomienda ? $factura->encomienda : null;
    $rem = $enc ? ($enc->remitente ?? null) : null;
    $dest = $enc ? ($enc->destinatario ?? null) : null;

    $fmtActa = function ($raw) {
        $raw = trim((string) ($raw ?? ''));
        if ($raw === '') {
            return 'NO-00000';
        }
        if (preg_match('/^NO[\s\-]/i', $raw)) {
            return $raw;
        }
        return 'NO-' . ltrim(preg_replace('/^NO[\s\-]*/i', '', $raw), '-');
    };
    $numeroNota = $fmtActa($factura->numero_acta ?? null);
    if ($numeroNota === 'NO-00000' && isset($factura->id)) {
        $numeroNota = 'NO-' . str_pad((string) $factura->id, 5, '0', STR_PAD_LEFT);
    }

    $servicioTxt = function ($tipo) {
        return ($tipo ?? 'maritimo') === 'aereo' ? 'Aéreo' : 'Marítimo';
    };

    $telDest = $dest ? trim(implode(' / ', array_filter([$dest->telefono_1 ?? null, $dest->telefono_2 ?? null]))) : '';

    $dirRemParts = $rem ? array_filter([
        $rem->direccion ?? null,
        trim(implode(', ', array_filter([$rem->ciudad ?? null, $rem->estado ?? null]))),
    ]) : [];
    $dirRem = $rem ? implode(', ', $dirRemParts) : '';

    $telRemDisplay = $rem && !empty($rem->telefono) ? $rem->telefono : '';
    if ($telRemDisplay !== '' && !str_contains($telRemDisplay, '(')) {
        $telRemDisplay = '(' . str_replace(['(', ')'], '', $telRemDisplay) . ')';
    }

    $dirDestParts = $dest ? array_filter([
        $dest->direccion ?? null,
        $dest->referencias ?? null,
        trim(implode(', ', array_filter([$dest->ciudad ?? null, $dest->departamento ?? null]))),
    ]) : [];
    $dirDest = $dest ? implode('. ', $dirDestParts) : '';

    $itemRows = collect();
    $subtotalCalc = 0.0;
    if ($enc && $enc->items && $enc->items->isNotEmpty()) {
        $itemRows = $enc->items;
        $subtotalCalc = (float) $itemRows->sum(fn ($i) => (float) ($i->monto_total_item ?? 0));
    } else {
        foreach ($factura->paquetes ?? [] as $paquete) {
            $itemRows->push($paquete);
        }
        foreach ($itemRows as $paquete) {
            $subtotalCalc += (float) (is_object($paquete) ? ($paquete->monto_calculado ?? 0) : ($paquete['monto_calculado'] ?? 0));
        }
    }

    $delivery = (float) ($factura->delivery ?? 0);
    $grandTotal = $subtotalCalc + $delivery;

    $logoCh = public_path('logo_skylinkone.png');

    $entNombre = optional($dest)->nombre_completo;
    $entDirFinal = $dirDest !== '' ? $dirDest : 'Km 11 carretera Masaya, de la entrada al Colegio Pureza, 100 mts al Este.';
    $entTelFinal = $telDest !== '' ? $telDest : '+505 8928 8565';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de cobro {{ $numeroNota }} - CH Logistics</title>
    <style>
        @page { margin: 0px 28px 22px 28px; }
        * { box-sizing: border-box; }
        /* Una sola familia en todo el PDF (DomPDF / UTF-8) */
        html, body, table, thead, tbody, tfoot, tr, th, td, div, span, strong, p, small, b, i, em {
            font-family: DejaVu Sans, sans-serif;
        }
        body {
            color: #334155;
            margin: 0;
            padding: 0;
            font-size: 10pt;
            font-weight: normal;
            line-height: 1.45;
            letter-spacing: normal;
        }
        .page { max-width: 100%; margin: 0 auto; padding: 0; background: #fff; }
        .logo-wrap { margin: -46px 0 -10px 0; padding: 0; line-height: 0; vertical-align: top; position: relative; top: -22px; }
        .logo-ch { display: block; width: 172pt; height: auto; }
        .header-tbl { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .header-tbl td { vertical-align: top; }
        /* Misma fila pero padding explícito (evita que .header-tbl td { padding:0 } lo anule) */
        .header-tbl td.header-left {
            width: 50%;
            padding: 0 28px 0 0;
        }
        .header-tbl td.header-right {
            width: 50%;
            padding: 18px 0 0 0;
            vertical-align: top;
        }
        /* Línea vertical solo junto al texto (nota + Nicaragua); no ocupa toda la altura de la fila */
        .header-right-line {
            display: block;
            border-left: 2px solid #0f172a;
            padding-left: 26px;
            padding-top: 0;
            padding-bottom: 0px;
            margin: 0;
        }
        .addr-us {
            font-size: 9pt;
            font-weight: normal;
            line-height: 1.15;
            color: #15537c;
            margin-top: -52px;
            position: relative;
            top: -24px;
        }
        .doc-title { font-size: 14pt; font-weight: 700; color: #0f172a; }
        .doc-num { font-size: 11pt; font-weight: 700; color: #15537c; margin-left: 8px; }
        .addr-ni {
            margin-top: 2px;
            font-size: 9pt;
            font-weight: normal;
            line-height: 1.25;
            color: #15537c;
        }
        /* Alineado con el texto a la derecha de la línea (2px borde + 26px padding) */
        .header-tbl tr.section-row td { vertical-align: top; padding-top: 0; }
        .header-tbl tr.section-row td.header-left { padding-top: 0; }
        .header-tbl tr.section-row td.header-right {
            padding: 0 0 0 28px;
            text-align: right;
        }
        /* Sube fecha/pago y datos del cliente reduciendo el hueco bajo la fila del logo */
        .section-pull-up { margin-top: -14px; }
        .sh { font-size: 10pt; font-weight: 700; color: #0f172a; margin-bottom: 3px; margin-top: 0; }
        .sh-right { text-align: right; }
        .fecha-big { font-size: 12pt; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .pago-value { text-align: right; font-size: 10pt; font-weight: normal; color: #15537c; line-height: 1.4; }
        .client-name { font-size: 10pt; font-weight: 700; color: #0d3d5c; margin-bottom: 4px; }
        .client-line { font-size: 10pt; font-weight: normal; color: #15537c; line-height: 1.3; margin-bottom: 1px; }
        .items-table { width: 100%; border-collapse: collapse; font-size: 10pt; margin-top: 22px; border-top: 2px solid #0f172a; }
        .items-table thead th {
            padding: 8px 8px;
            font-weight: 700;
            font-size: 10pt;
            text-align: left;
            color: #0f172a;
            border-bottom: 1px solid #0f172a;
            background: #f8fafc;
        }
        .items-table thead th.r { text-align: right; }
        .items-table tbody td {
            padding: 8px 8px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            font-weight: normal;
            vertical-align: top;
        }
        .items-table tbody td.col-num { color: #15537c; font-weight: normal; }
        .items-table tbody td.r { text-align: right; font-weight: normal; color: #0d3d5c; }
        .items-table tbody tr:last-child td { border-bottom: 1px solid #0f172a; }
        .totals-wrap { margin-top: 14px; text-align: right; }
        .totals-wrap .line { margin-bottom: 4px; font-size: 10pt; color: #0f172a; font-weight: normal; }
        .totals-wrap .sub { font-weight: 700; color: #15537c; font-size: 10pt; }
        .totals-wrap .grand { font-weight: 700; color: #0d3d5c; font-size: 11pt; margin-top: 8px; }
        .firma { margin-top: 28px; }
        .firma-line { border-bottom: 1px solid #0f172a; display: block; width: 92%; max-width: 420px; margin-bottom: 8px; }
        .firma-cap { font-size: 9pt; font-weight: normal; color: #475569; }
        .entrega { margin-top: 24px; }
        .entrega-hr { border: none; border-top: 1px solid #cbd5e1; width: 62%; margin: 0 auto 10px; }
        .entrega-title {
            text-align: center;
            font-weight: 700;
            font-size: 10pt;
            margin: 0 0 10px 0;
            color: #0f172a;
        }
        .entrega-tbl { width: 100%; border-collapse: collapse; font-size: 10pt; margin-top: 10px; }
        .entrega-tbl th {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            font-weight: 700;
            font-size: 10pt;
            text-align: center;
            background: #f1f5f9;
            color: #0f172a;
        }
        .entrega-tbl td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            color: #334155;
            font-weight: normal;
            line-height: 1.45;
            vertical-align: top;
        }
        .foot { margin-top: 20px; }
        .foot-tbl { width: 100%; border-collapse: collapse; }
        .foot-tbl td { vertical-align: top; }
        .foot-qr { width: 76px; }
        .foot-qr img { width: 70px; height: 70px; display: block; }
        .disc { font-size: 8pt; color: #64748b; line-height: 1.42; text-transform: uppercase; font-weight: normal; }
        .disc strong { display: block; margin-bottom: 4px; font-size: 8pt; text-transform: none; color: #0f172a; font-weight: 700; }
        .ph { margin-top: 0; }
    </style>
</head>
<body>
    <div class="page">
        <table class="header-tbl" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header-left">
                    @if(is_file($logoCh))
                        <div class="logo-wrap">
                            <img class="logo-ch" src="{{ $logoCh }}" alt="CH Logistics">
                        </div>
                    @endif
                    <div class="addr-us">
                        <div>8307 NW 68TH ST Miami FL 33166</div>
                        <div class="ph">www.enviosch.com</div>
                    </div>
                </td>
                <td class="header-right">
                    <div class="header-right-line">
                        <div>
                            <span class="doc-title">Nota de cobro</span>
                            <span class="doc-num">{{ $numeroNota }}</span>
                        </div>
                        <div class="addr-ni">
                            <div>Km 11 carretera Masaya, de la entrada al Colegio Pureza, 100 mts al Este.</div>
                            <div class="ph">+505 8928 8565</div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="section-row">
                <td class="header-left">
                    <div class="section-pull-up">
                        <div class="sh">Datos del cliente</div>
                        @if($rem)
                            <div class="client-name">{{ $rem->nombre_completo }}</div>
                            @if($telRemDisplay !== '')
                                <div class="client-line">{{ $telRemDisplay }}</div>
                            @endif
                            @if($dirRem !== '')
                                <div class="client-line">{{ $dirRem }}</div>
                            @endif
                        @elseif($factura->cliente)
                            <div class="client-name">{{ $factura->cliente->nombre_completo ?? '—' }}</div>
                            @if(!empty($factura->cliente->telefono))
                                <div class="client-line">{{ $factura->cliente->telefono }}</div>
                            @endif
                            @if(!empty($factura->cliente->direccion))
                                <div class="client-line">{{ $factura->cliente->direccion }}</div>
                            @endif
                        @else
                            <div class="client-name">—</div>
                        @endif
                    </div>
                </td>
                <td class="header-right">
                    <div class="section-pull-up">
                        <div class="fecha-big">{{ isset($factura->fecha_factura) ? \Carbon\Carbon::parse($factura->fecha_factura)->format('d/m/Y') : '—' }}</div>
                        <div class="sh sh-right">Forma de pago:</div>
                        <div class="pago-value">Transferencia / Zelle / Efectivo</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="items-table" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th style="width:14%;">WRH</th>
                    <th style="width:38%;">Detalle</th>
                    <th style="width:14%;">Servicio</th>
                    <th style="width:14%; text-align:center;">delivery</th>
                    <th class="r" style="width:13%;">Precio</th>
                    <th class="r" style="width:17%;">Total</th>
                </tr>
            </thead>
            <tbody>
            @if($enc && $itemRows->isNotEmpty())
                @foreach($itemRows as $item)
                    @php
                        $wrh = str_pad((string) $loop->iteration, 5, '0', STR_PAD_LEFT);
                        $det = trim((string) ($item->descripcion ?? ''));
                        if ($det === '') {
                            $det = trim((string) ($item->tipo_item ?? ''));
                        }
                        if ($det === '') {
                            $det = 'Bulto';
                        }
                        if (!empty($item->largo_in) && !empty($item->ancho_in) && !empty($item->alto_in)) {
                            $det .= ' (' . $item->largo_in . '×' . $item->ancho_in . '×' . $item->alto_in . ' in)';
                        }
                        $srv = $servicioTxt($enc->tipo_servicio ?? 'maritimo');
                        $precio = (float) ($item->tarifa_manual ?? 0);
                        $linTot = (float) ($item->monto_total_item ?? 0);
                    @endphp
                    <tr>
                        <td class="col-num">{{ $wrh }}</td>
                        <td>{{ $det }}</td>
                        <td class="col-num">{{ $srv }}</td>
                        <td style="text-align:center; color:#15537c; font-weight:700;">
                            @if(!empty($item->incluye_delivery))
                                @php $dm = $item->delivery_monto; @endphp
                                @php $dmNum = is_null($dm) ? 0 : (float) $dm; @endphp
                                @if($dm > 0)
                                    ${{ number_format($dmNum, 2) }}
                                @else
                                    Incl.
                                @endif
                            @endif
                        </td>
                        <td class="r">${{ number_format($precio, 2) }}</td>
                        <td class="r">${{ number_format($linTot, 2) }}</td>
                    </tr>
                @endforeach
            @else
                @foreach($factura->paquetes ?? [] as $paquete)
                    @php
                        $valor = is_object($paquete) ? ($paquete->monto_calculado ?? 0) : ($paquete['monto_calculado'] ?? 0);
                        $servicio = is_object($paquete) ? ($paquete->servicio ?? null) : ($paquete['servicio'] ?? null);
                        $srvStr = $servicio ? (is_object($servicio) ? ($servicio->tipo_servicio ?? '—') : $servicio) : '—';
                    @endphp
                    <tr>
                        <td class="col-num">{{ is_object($paquete) ? ($paquete->numero_guia ?? '-') : ($paquete['numero_guia'] ?? '-') }}</td>
                        <td>{{ is_object($paquete) ? ($paquete->notas ?? '-') : ($paquete['notas'] ?? '-') }}</td>
                        <td class="col-num">{{ $srvStr }}</td>
                        <td style="text-align:center; color:#15537c; font-weight:700;">—</td>
                        <td class="r">${{ number_format(is_object($paquete) ? ($paquete->tarifa_manual ?? ($paquete->tarifa ?? 1)) : ($paquete['tarifa_manual'] ?? ($paquete['tarifa'] ?? 1)), 2) }}</td>
                        <td class="r">${{ number_format($valor, 2) }}</td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>

        <div class="totals-wrap">
            <div class="line">Subtotal: <span class="sub">${{ number_format($subtotalCalc, 2) }}</span></div>
            @if($delivery > 0)
                <div class="line">Delivery: <span class="sub">${{ number_format($delivery, 2) }}</span></div>
            @endif
            <div class="line grand">Total: <span class="sub">${{ number_format($grandTotal, 2) }}</span></div>
        </div>

        <div class="firma">
            <span class="firma-line"></span>
            <div class="firma-cap">Firma de recibido del cliente</div>
        </div>

        <div class="entrega">
            <hr class="entrega-hr">
            <div class="entrega-title">DATOS DE ENTREGA</div>
            <hr class="entrega-hr">
            <table class="entrega-tbl">
                <tr>
                    <th style="width:22%;">Nombre receptor</th>
                    <th style="width:48%;">Dirección de entrega</th>
                    <th style="width:30%;">Teléfono</th>
                </tr>
                <tr>
                    <td>{{ $entNombre ?: '—' }}</td>
                    <td>{{ $entDirFinal }}</td>
                    <td>{{ $entTelFinal }}</td>
                </tr>
            </table>
        </div>

        <div class="foot">
            <table class="foot-tbl" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="foot-qr">
                        @if(file_exists(public_path('qr_ch_logistics.png')))
                            <img src="{{ public_path('qr_ch_logistics.png') }}" alt="QR">
                        @else
                            <div style="width:70px;height:70px;border:1px solid #ddd;background:#fafafa;"></div>
                        @endif
                    </td>
                    <td class="disc">
                        <strong>DISCLAIMER:</strong>
                        ESTÁ PROHIBIDO ENVIAR ARMAS DE FUEGO, DROGAS, ARMAS Y CUALQUIER SUSTANCIA ILEGAL A TRAVÉS DE ENVIOS CH LOGISTICS, Y CUALQUIER VIOLACIÓN DE ESTA POLÍTICA RESULTARÁ EN POSIBLES ACCIONES LEGALES CONTRA EL CLIENTE.
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
