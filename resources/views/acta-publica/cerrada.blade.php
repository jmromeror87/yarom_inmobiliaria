<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta no disponible — Serviarrendar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        @if(isset($acta))
        <h1 class="text-2xl font-extrabold text-slate-800 mb-2">Acta oficialmente cerrada</h1>
        <p class="text-slate-500 mb-6">
            El acta de entrega del inmueble <strong>{{ $acta->property?->codigo }}</strong>
            fue cerrada el <strong>{{ $acta->fecha_firma?->format('d/m/Y') ?? $acta->updated_at->format('d/m/Y') }}</strong>
            y ya no puede ser modificada.
        </p>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 text-left text-sm text-slate-600 space-y-2 mb-6">
            <p><span class="font-semibold">N° Acta:</span> {{ $acta->numero }}</p>
            <p><span class="font-semibold">Inmueble:</span> {{ $acta->property?->direccion }}</p>
            <p><span class="font-semibold">Arrendatario:</span> {{ $acta->arrendatario?->nombre_completo }}</p>
        </div>
        @else
        <h1 class="text-2xl font-extrabold text-slate-800 mb-2">Este enlace ya no está disponible</h1>
        <p class="text-slate-500 mb-6">
            El acta de entrega fue cerrada y el enlace fue desactivado por seguridad.<br>
            Si necesita una copia del documento, comuníquese con la inmobiliaria.
        </p>
        @endif

        <div class="bg-slate-100 rounded-xl p-4 text-sm text-slate-600">
            <p class="font-semibold text-slate-700 mb-1">Serviarrendar S.A.S</p>
            <p>📞 (607) 561 0274 &nbsp;|&nbsp; 📱 +57 318 693 4710</p>
            <p class="mt-1">Cra 13 N° 11-15 Of. 103, Ocaña</p>
        </div>
    </div>
</body>
</html>
