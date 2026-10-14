@props(['titulo' => null])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#132f3d">
    <title>{{ $titulo ? "$titulo · " : '' }}{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-fondo font-sans text-slate-700 antialiased">
    <x-navbar />

    <main class="flex-1 pb-16">
        {{ $slot }}
    </main>

    <footer class="bg-noche text-white/60 print:hidden">
        <div class="contenedor flex flex-col gap-4 py-10 text-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex size-8 items-center justify-center rounded-lg bg-white/10 text-cielo"><x-icono nombre="pulso" class="size-4" /></span>
                <span class="font-semibold text-white">{{ config('app.name') }}</span>
            </div>
            <p class="max-w-md text-xs leading-relaxed">Herramienta orientativa: no reemplaza la valoración de un profesional de la salud. Tus datos se quedan en tu navegador.</p>
        </div>
    </footer>
</body>
</html>
