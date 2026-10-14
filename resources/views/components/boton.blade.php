@props(['href' => null, 'variante' => 'primario', 'icono' => null])
@php
    $clases = 'inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cielo '
        . match ($variante) {
            'primario' => 'bg-petroleo text-white shadow-sm hover:bg-petroleo-oscuro',
            'cta' => 'bg-naranja text-white shadow-sm shadow-naranja/30 hover:bg-[#d95e2b]',
            'secundario' => 'border border-slate-300 bg-white text-slate-800 hover:bg-slate-50',
            'claro' => 'border border-white/20 bg-white/10 text-white hover:bg-white/20',
            'peligro' => 'border border-cafe/30 bg-white text-cafe hover:bg-cafe/5',
        };
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $clases]) }}>
        @if ($icono)<x-icono :nombre="$icono" class="size-4" />@endif{{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $clases]) }}>
        @if ($icono)<x-icono :nombre="$icono" class="size-4" />@endif{{ $slot }}
    </button>
@endif
