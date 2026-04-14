@extends('layouts.app-new')

@section('title', 'Nuevo Remitente')
@section('navbar-title', 'Nuevo Remitente')

@section('content')
<div class="mx-auto w-full max-w-4xl space-y-6">
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
            <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="mb-4 text-xl font-bold text-slate-800">Registrar remitente</h1>
        <form method="POST" action="{{ route('remitentes.store') }}">
            @php($remitente = null)
            @php($buttonText = 'Guardar')
            @include('remitentes._form')
        </form>
    </div>
</div>
@endsection
