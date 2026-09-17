<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sin acceso - CH Logistics</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center px-4">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-2xl">!</div>
        <h1 class="text-xl font-bold text-slate-800">Sin módulos asignados</h1>
        <p class="mt-2 text-sm text-slate-500">
            Tu usuario está activo, pero no tiene permisos para entrar a ningún módulo.
            Pide al administrador que te asigne acceso (por ejemplo Inventario o Dashboard) en Usuarios.
        </p>
        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button type="submit" class="w-full rounded-xl bg-[#15537c] py-3 font-semibold text-white hover:bg-[#0f3d5c]">
                Cerrar sesión
            </button>
        </form>
    </div>
</body>
</html>
