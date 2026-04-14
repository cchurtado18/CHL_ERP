@extends('layouts.app-new')

@section('title', 'Editar Encomienda Familiar')
@section('navbar-title', 'Editar Encomienda Familiar')

@section('content')
<div class="mx-auto w-full max-w-[1400px] space-y-6">
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
            <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="mb-4 text-xl font-bold text-slate-800">Editar encomienda {{ $encomienda->codigo }}</h1>
        <form method="POST" action="{{ route('encomiendas.update', $encomienda->id) }}" enctype="multipart/form-data">
            @method('PUT')
            @php($buttonText = 'Actualizar encomienda')
            @include('encomiendas._form')
        </form>
    </div>
</div>
@endsection
