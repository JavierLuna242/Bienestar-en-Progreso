@props(['titulo', 'subtitulo' => null, 'antetitulo' => null])
{{-- Banda oscura con el título de la página; el contenido siguiente se superpone con -mt-16. --}}
<section class="banda pb-24 pt-10 sm:pt-14">
    <div class="contenedor flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-2xl">
            @if ($antetitulo)
                <p class="text-sm font-medium text-cielo">{{ $antetitulo }}</p>
            @endif
            <h1 class="titulo mt-2 text-white">{{ $titulo }}</h1>
            @if ($subtitulo)
                <p class="mt-3 text-base leading-relaxed text-white/70">{{ $subtitulo }}</p>
            @endif
        </div>
        @isset($acciones)
            <div class="flex flex-wrap gap-2 print:hidden">{{ $acciones }}</div>
        @endisset
    </div>
    {{ $slot }}
</section>
