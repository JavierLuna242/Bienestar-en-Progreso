<header class="sticky top-0 z-30 border-b border-white/10 bg-noche/95 text-white backdrop-blur print:hidden">
    <nav class="contenedor flex h-16 items-center justify-between gap-3">
        <a href="{{ route('inicio') }}" class="flex items-center gap-2.5 font-semibold tracking-tight">
            <span class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-cielo to-petroleo text-white shadow-sm">
                <x-icono nombre="pulso" class="size-4" />
            </span>
            <span class="hidden sm:inline">Bienestar <span class="text-cielo">en Progreso</span></span>
            <span class="sm:hidden">Bienestar</span>
        </a>
        <div class="flex items-center gap-1 text-sm">
            @foreach (['valoracion' => ['Valoración', 'portapapeles'], 'progreso' => ['Progreso', 'tendencia']] as $ruta => [$texto, $icono])
                <a href="{{ route($ruta) }}"
                   @if (request()->routeIs($ruta)) aria-current="page" @endif
                   @class([
                       'flex items-center gap-2 rounded-lg px-3 py-2 font-medium',
                       'bg-white/10 text-white' => request()->routeIs($ruta),
                       'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs($ruta),
                   ])>
                    <x-icono :nombre="$icono" class="hidden size-4 sm:block" />{{ $texto }}
                </a>
            @endforeach
        </div>
    </nav>
</header>
