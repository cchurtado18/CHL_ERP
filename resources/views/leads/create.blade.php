@extends('layouts.app-new')

@section('title', 'Crear Lead')
@section('navbar-title', 'Leads')

@section('content')
<div class="mx-auto w-full max-w-[1100px] space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="mb-1 text-2xl font-bold text-slate-900">Nuevo Lead</h1>
        <p class="text-sm text-slate-600">Registra un prospecto de campaña y programa su siguiente contacto.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('leads.store') }}" method="POST" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @include('leads._form', ['buttonText' => 'Guardar lead'])
    </form>
</div>
@endsection
