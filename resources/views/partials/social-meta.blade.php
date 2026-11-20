{{--
    Vista previa al compartir el enlace (WhatsApp, Facebook, Telegram, X…).
    Uso: @include('partials.social-meta', ['title' => '...', 'description' => '...'])
    Si cambias la imagen, sube el número de versión (?v=) para que WhatsApp la vuelva a descargar.
--}}
@php
    $metaTitle = $title ?? 'CH Logistics · Envíos y paquetería';
    $metaDescription = $description ?? 'Rastrea tu paquete en línea y consulta tus envíos desde cualquier lugar.';
    $metaImage = asset('og-image.jpg').'?v=1';
@endphp
<meta name="description" content="{{ $metaDescription }}">
<meta name="theme-color" content="#0d3554">
<link rel="canonical" href="{{ url()->current() }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="CH Logistics">
<meta property="og:locale" content="es_NI">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:secure_url" content="{{ $metaImage }}">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="CH Logistics — Tus envíos, siempre a la vista.">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $metaImage }}">
