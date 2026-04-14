@php
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

    $logoCh = public_path('logo_skylinkone.png');
    $cliente = $factura->cliente ?? null;

    $paquetes = collect($factura->paquetes ?? []);
    $subtotalCalc = 0.0;
    $pesoTotal = 0.0;
    $lbsAereas = 0.0;
    $lbsMaritimas = 0.0;
    foreach ($paquetes as $p) {
        $subtotalCalc += (float) (is_object($p) ? ($p->monto_calculado ?? 0) : ($p['monto_calculado'] ?? 0));
        $peso = (float) (is_object($p) ? ($p->peso_lb ?? 0) : ($p['peso_lb'] ?? 0));
        $pesoTotal += $peso;
        $srvRaw = is_object($p) ? ($p->servicio ?? null) : ($p['servicio'] ?? null);
        $srvTipo = $srvRaw ? strtolower((string) (is_object($srvRaw) ? ($srvRaw->tipo_servicio ?? '') : $srvRaw)) : '';
        $srvNorm = strtr($srvTipo, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'Á' => 'a', 'É' => 'e']);
        if (str_contains($srvNorm, 'aer')) {
            $lbsAereas += $peso;
        } else {
            // Por defecto lo tomamos como marítimo para no perder libras sin clasificar.
            $lbsMaritimas += $peso;
        }
    }
    $delivery = (float) ($factura->delivery ?? 0);
    $grandTotal = $subtotalCalc + $delivery;

    $telCliente = trim((string) ($cliente->telefono ?? ''));
    $dirCliente = trim((string) ($cliente->direccion ?? ''));
    $nombreCliente = trim((string) ($cliente->nombre_completo ?? 'Cliente'));

    $fechaFactura = isset($factura->fecha_factura) ? \Carbon\Carbon::parse($factura->fecha_factura)->format('d/m/Y') : '—';
    $entregaNombre = trim((string) ($factura->entrega_nombre ?? ''));
    $entregaCedula = trim((string) ($factura->entrega_cedula ?? ''));
    $entregaTelefono = trim((string) ($factura->entrega_telefono ?? ''));
    if ($entregaNombre === '') {
        $entregaNombre = $nombreCliente !== '' ? $nombreCliente : '—';
    }
    if ($entregaTelefono === '') {
        $entregaTelefono = $telCliente !== '' ? $telCliente : '—';
    }
    if ($entregaCedula === '') {
        $entregaCedula = '—';
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de cobro {{ $numeroNota }} - CH Logistics</title>
    <style>
        @page { margin: 0px 28px 22px 28px; }
        * { box-sizing: border-box; }
        html, body, table, thead, tbody, tfoot, tr, th, td, div, span, strong, p, small, b, i, em {
            font-family: DejaVu Sans, sans-serif;
        }
        body { color: #334155; margin: 0; padding: 0; font-size: 10pt; font-weight: normal; line-height: 1.45; letter-spacing: normal; }
        .page { max-width: 100%; margin: 0 auto; padding: 0; background: #fff; }

        .logo-wrap { margin: -46px 0 -10px 0; padding: 0; line-height: 0; vertical-align: top; position: relative; top: -22px; }
        .logo-ch { display: block; width: 172pt; height: auto; }
        .header-tbl { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .header-tbl td { vertical-align: top; }
        .header-tbl td.header-left { width: 50%; padding: 0 28px 0 0; }
        .header-tbl td.header-right { width: 50%; padding: 18px 0 0 0; vertical-align: top; }

        .header-right-line { display: block; border-left: 2px solid #0f172a; padding-left: 26px; margin: 0; }
        .addr-us { font-size: 9pt; font-weight: normal; line-height: 1.15; color: #15537c; margin-top: -52px; position: relative; top: -24px; }
        .doc-title { font-size: 14pt; font-weight: 700; color: #0f172a; }
        .doc-num { font-size: 11pt; font-weight: 700; color: #15537c; margin-left: 8px; }
        .addr-ni { margin-top: 2px; font-size: 9pt; font-weight: normal; line-height: 1.25; color: #15537c; }

        .header-tbl tr.section-row td { vertical-align: top; padding-top: 0; }
        .header-tbl tr.section-row td.header-left { padding-top: 0; }
        .header-tbl tr.section-row td.header-right { padding: 0 0 0 28px; text-align: right; }
        .section-pull-up { margin-top: -14px; }
        .sh { font-size: 10pt; font-weight: 700; color: #0f172a; margin-bottom: 3px; margin-top: 0; }
        .sh-right { text-align: right; }
        .fecha-big { font-size: 12pt; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .pago-value { text-align: right; font-size: 10pt; font-weight: normal; color: #15537c; line-height: 1.4; }
        .client-name { font-size: 10pt; font-weight: 700; color: #0d3d5c; margin-bottom: 4px; }
        .client-line { font-size: 10pt; font-weight: normal; color: #15537c; line-height: 1.3; margin-bottom: 1px; }

        .items-table { width: 100%; border-collapse: collapse; font-size: 10pt; margin-top: 22px; border-top: 2px solid #0f172a; table-layout: fixed; }
        .items-table thead th {
            padding: 8px 8px;
            font-weight: 700;
            font-size: 9pt;
            text-align: left;
            color: #0f172a;
            border-bottom: 1px solid #0f172a;
            background: #f8fafc;
            white-space: nowrap;
        }
        .items-table thead th.r { text-align: right; }
        .items-table thead th.c { text-align: center; }
        .items-table tbody td {
            padding: 8px 8px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            font-weight: normal;
            vertical-align: top;
            word-break: break-word;
            font-size: 9pt;
        }
        .items-table tbody td.col-num { color: #15537c; font-weight: 700; }
        .items-table tbody td.r { text-align: right; font-weight: normal; color: #0d3d5c; }
        .items-table tbody td.c { text-align: center; }
        .items-table .tracking-col {
            font-size: 7.8pt;
            text-align: left;
            padding-left: 1px;
            padding-right: 3px;
            letter-spacing: 0;
        }
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
        .entrega-title { text-align: center; font-weight: 700; font-size: 10pt; margin: 0 0 10px 0; color: #0f172a; }
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
                        <div class="client-name">{{ $nombreCliente }}</div>
                        @if($telCliente !== '')
                            <div class="client-line">{{ $telCliente }}</div>
                        @endif
                        @if($dirCliente !== '')
                            <div class="client-line">{{ $dirCliente }}</div>
                        @endif
                    </div>
                </td>
                <td class="header-right">
                    <div class="section-pull-up">
                        <div class="fecha-big">{{ $fechaFactura }}</div>
                        <div class="sh sh-right">Forma de pago:</div>
                        <div class="pago-value">Transferencia / Zelle / Efectivo</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="items-table" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th style="width:11%;">WRH</th>
                    <th style="width:20%;">Descripción</th>
                    <th class="tracking-col" style="width:24%;">Tracking</th>
                    <th style="width:13%;">Servicio</th>
                    <th class="c" style="width:9%;">Peso</th>
                    <th class="r" style="width:11%;">P. Unitario</th>
                    <th class="r" style="width:12%;">Valor</th>
                </tr>
            </thead>
            <tbody>
            @forelse($paquetes as $paquete)
                @php
                    $guia = is_object($paquete) ? ($paquete->numero_guia ?? '-') : ($paquete['numero_guia'] ?? '-');
                    $desc = trim((string) (is_object($paquete) ? ($paquete->notas ?? '') : ($paquete['notas'] ?? '')));
                    if ($desc === '') {
                        $desc = 'Paquete';
                    }
                    $trk = is_object($paquete) ? ($paquete->tracking_codigo ?? '-') : ($paquete['tracking_codigo'] ?? '-');
                    $srvRaw = is_object($paquete) ? ($paquete->servicio ?? null) : ($paquete['servicio'] ?? null);
                    $srv = $srvRaw ? (is_object($srvRaw) ? ($srvRaw->tipo_servicio ?? '-') : $srvRaw) : 'Maritimo';
                    $peso = (float) (is_object($paquete) ? ($paquete->peso_lb ?? 0) : ($paquete['peso_lb'] ?? 0));
                    $unit = (float) (is_object($paquete) ? ($paquete->tarifa_manual ?? ($paquete->tarifa ?? 0)) : ($paquete['tarifa_manual'] ?? ($paquete['tarifa'] ?? 0)));
                    $valor = (float) (is_object($paquete) ? ($paquete->monto_calculado ?? 0) : ($paquete['monto_calculado'] ?? 0));
                @endphp
                <tr>
                    <td class="col-num">{{ $guia }}</td>
                    <td>{{ $desc }}</td>
                    <td class="col-num tracking-col">{{ $trk }}</td>
                    <td class="col-num">{{ ucfirst((string) $srv) }}</td>
                    <td class="c">{{ rtrim(rtrim(number_format($peso, 2, '.', ''), '0'), '.') }}</td>
                    <td class="r">${{ number_format($unit, 2) }}</td>
                    <td class="r">${{ number_format($valor, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:#64748b;">Sin paquetes para mostrar.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="totals-wrap">
            <div class="line">
                Lbs Aéreas: <span class="sub">{{ rtrim(rtrim(number_format($lbsAereas, 2, '.', ''), '0'), '.') }}</span>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Lbs Marítimas: <span class="sub">{{ rtrim(rtrim(number_format($lbsMaritimas, 2, '.', ''), '0'), '.') }}</span>
            </div>
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
                    <th style="width:34%;">Entrega a</th>
                    <th style="width:33%;">Cedula Receptor</th>
                    <th style="width:33%;">Telefono</th>
                </tr>
                <tr>
                    <td>{{ $entregaNombre }}</td>
                    <td>{{ $entregaCedula }}</td>
                    <td>{{ $entregaTelefono }}</td>
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
