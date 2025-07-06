<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #{{ $factura->id }} - SkyLink One</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            margin: 0;
            padding: 0;
            font-size: 13px;
        }
        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #1A1A5E;
            padding: 20px 0 10px 0;
        }
        .logo {
            width: 180px;
        }
        .factura-info {
            flex: 1;
            text-align: right;
        }
        .factura-info h2 {
            margin: 0;
            color: #1A1A5E;
            font-size: 1.3rem;
        }
        .factura-info table {
            margin-left: auto;
        }
        .section {
            margin: 18px 0 0 0;
        }
        .section-title {
            color: #1A1A5E;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 2px 6px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th {
            background: #1A1A5E;
            color: #fff;
            padding: 6px 4px;
            font-size: 13px;
        }
        .table td {
            border-bottom: 1px solid #e9ecef;
            padding: 5px 4px;
        }
        .table-striped tr:nth-child(even) td {
            background: #f4f4f4;
        }
        .totals {
            margin-top: 18px;
            width: 100%;
        }
        .totals td {
            padding: 4px 6px;
        }
        .totals .label {
            color: #1A1A5E;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .pdf-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .pdf-table th {
            background: #0d6efd;
            color: #fff;
            padding: 8px 6px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        .pdf-table td {
            background: #f8f9fa;
            padding: 7px 6px;
            border-bottom: 1px solid #dee2e6;
            text-align: center;
        }
        .pdf-table tr:nth-child(even) td {
            background: #e9ecef;
        }
        .pdf-table tr:last-child td {
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('logo_skylinkone.png') }}" class="logo" alt="SkyLink One Logo">
        <div class="factura-info">
            <h2>Factura</h2>
            <table>
                <tr>
                    <td><strong>Fecha:</strong></td>
                    <td>{{ \Carbon\Carbon::parse($factura->fecha_factura)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>N° de factura:</strong></td>
                    <td>{{ $factura->id }}</td>
                </tr>
                <tr>
                    <td><strong>N° de acta:</strong></td>
                    <td>{{ $factura->numero_acta }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <table class="info-table">
            <tr>
                <td class="section-title">Para:</td>
                <td class="section-title">Envia:</td>
            </tr>
            <tr>
                <td>
                    @if(!empty($factura->cliente->nombre_completo))
                        {{ $factura->cliente->nombre_completo }}<br>
                    @endif
                    @if(!empty($factura->cliente->direccion))
                        {{ $factura->cliente->direccion }}<br>
                    @endif
                    @if(!empty($factura->cliente->telefono))
                        {{ $factura->cliente->telefono }}
                    @endif
                </td>
                <td>
                    SkyLink One<br>
                    Managua<br>
                    85607503
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Warehouse</th>
                    <th>Descripción</th>
                    <th>Tracking</th>
                    <th>Servicio</th>
                    <th>Peso (lb)</th>
                    <th>Precio Unitario</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
            @php $total = 0; @endphp
            @foreach($factura->paquetes as $paquete)
                <tr>
                    <td>{{ is_object($paquete) ? ($paquete->numero_guia ?? '-') : ($paquete['numero_guia'] ?? '-') }}</td>
                    <td>{{ is_object($paquete) ? ($paquete->notas ?? '-') : ($paquete['notas'] ?? '-') }}</td>
                    <td>{{ is_object($paquete) ? ($paquete->tracking_codigo ?? '-') : ($paquete['tracking_codigo'] ?? '-') }}</td>
                    <td>
                        @php $servicio = is_object($paquete) ? ($paquete->servicio ?? null) : ($paquete['servicio'] ?? null); @endphp
                        @if($servicio)
                            {{ is_object($servicio) ? ($servicio->tipo_servicio ?? '-') : ($servicio ?? '-') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ is_object($paquete) ? ($paquete->peso_lb ?? '-') : ($paquete['peso_lb'] ?? '-') }}</td>
                    <td>${{ number_format(is_object($paquete) ? ($paquete->tarifa_manual ?? ($paquete->tarifa ?? 1.00)) : ($paquete['tarifa_manual'] ?? ($paquete['tarifa'] ?? 1.00)), 2) }}</td>
                    <td>${{ number_format(is_object($paquete) ? ($paquete->monto_calculado ?? 0) : ($paquete['monto_calculado'] ?? 0), 2) }}</td>
                </tr>
                @php $total += is_object($paquete) ? ($paquete->monto_calculado ?? 0) : ($paquete['monto_calculado'] ?? 0); @endphp
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Resumen de libras por servicio --}}
    @php
        function quitarTildesYMinusculas($cadena) {
            $cadena = mb_strtolower($cadena, 'UTF-8');
            $buscar  = array('á','é','í','ó','ú','ñ');
            $reempl = array('a','e','i','o','u','n');
            return str_replace($buscar, $reempl, $cadena);
        }
        $librasAereo = 0;
        $librasMaritimo = 0;
        $librasPieCubico = 0;
        foreach($factura->paquetes as $paquete) {
            $servicio = is_object($paquete) ? ($paquete->servicio ?? $paquete->servicio) : ($paquete['servicio'] ?? null);
            $tipo = is_object($servicio) ? ($servicio->tipo_servicio ?? $servicio) : ($servicio ?? '');
            $tipo = quitarTildesYMinusculas($tipo);
            $peso = is_object($paquete) ? ($paquete->peso_lb ?? 0) : ($paquete['peso_lb'] ?? 0);
            if(strpos($tipo, 'aereo') !== false || strpos($tipo, 'air') !== false) $librasAereo += floatval($peso);
            if(strpos($tipo, 'maritimo') !== false || strpos($tipo, 'mar') !== false) $librasMaritimo += floatval($peso);
            if(strpos($tipo, 'pie') !== false) $librasPieCubico += floatval($peso);
        }
    @endphp
    <div class="section" style="margin-top: 10px;">
        <table style="width: 100%; font-size: 13px;">
            @if($librasAereo > 0)
            <tr>
                <td><strong>Libras Aéreas:</strong> {{ number_format($librasAereo, 2) }} lb</td>
            </tr>
            @endif
            @if($librasMaritimo > 0)
            <tr>
                <td><strong>Libras Marítimas:</strong> {{ number_format($librasMaritimo, 2) }} lb</td>
            </tr>
            @endif
            @if($librasPieCubico > 0)
            <tr>
                <td><strong>Pies Cúbicos:</strong> {{ number_format($librasPieCubico, 2) }} cu ft</td>
            </tr>
            @endif
        </table>
    </div>

    <table class="totals">
        <tr>
            <td class="label text-right">Subtotal:</td>
            <td class="text-right">${{ number_format($total, 2) }}</td>
        </tr>
        <tr>
            <td class="label text-right">Delivery:</td>
            <td class="text-right">${{ number_format($factura->delivery ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label text-right">Total:</td>
            <td class="text-right">${{ number_format($total + ($factura->delivery ?? 0), 2) }}</td>
        </tr>
    </table>

    <div class="section" style="margin-top: 30px;">
        <span class="section-title">Nota:</span>
        <span>{{ $factura->nota }}</span>
    </div>
</body>
</html> 