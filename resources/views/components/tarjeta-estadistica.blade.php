@props(['titulo', 'valor' => '—', 'detalle' => '', 'icono' => null])
<div {{ $attributes->merge(['class' => 'tarjeta flex flex-col']) }}>
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-medium text-slate-500">{{ $titulo }}</p>
        @if ($icono)
            <span class="flex size-8 items-center justify-center rounded-lg bg-petroleo/10 text-petroleo"><x-icono :nombre="$icono" class="size-4" /></span>
        @endif
    </div>
    <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900" data-valor>{{ $valor }}</p>
    <p class="mt-1 text-sm text-slate-500" data-detalle>{{ $detalle }}</p>
    {{ $slot }}
</div>
