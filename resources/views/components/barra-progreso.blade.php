@props(['valor' => 0, 'etiqueta', 'color' => 'var(--petroleo)', 'sufijo' => '', 'icono' => null])
<div {{ $attributes }}>
    <div class="mb-2 flex items-center justify-between gap-3 text-sm">
        <span class="flex items-center gap-2 font-medium text-slate-700">
            @if ($icono)<x-icono :nombre="$icono" class="size-4 text-slate-400" />@endif{{ $etiqueta }}
        </span>
        <span class="font-semibold tabular-nums text-slate-900" data-texto>{{ $valor }}{{ $sufijo }}</span>
    </div>
    <div class="h-2 overflow-hidden rounded-full bg-slate-200/70" role="progressbar" aria-label="{{ $etiqueta }}"
         aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $valor }}">
        <div class="barra h-full rounded-full" data-barra style="width: {{ $valor }}%; background: {{ $color }}"></div>
    </div>
</div>
