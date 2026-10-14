@use('App\Services\WellnessScoreService')
@php
    $r = $resultado;
    $clave = $r['perfil']['clave'];
    $riesgo = $clave === 'riesgo';
    $tono = fn (int $v) => $v >= 70 ? 'var(--petroleo)' : ($v >= 40 ? 'var(--cielo)' : 'var(--cafe)');
    $etiquetas = array_map(fn ($a) => $a[0], WellnessScoreService::AREAS);

    $orden = $r['areas'];
    arsort($orden);
    $fuerte = array_key_first($orden);
    $foco = array_key_last($orden);

    // Escala de perfiles: [clave, etiqueta corta, desde, hasta, color]
    $escala = [
        ['riesgo', 'Riesgo', 0, 40, 'var(--cafe)'],
        ['construccion', 'En construcción', 40, 60, 'var(--naranja)'],
        ['activo', 'Activo', 60, 80, 'var(--cielo)'],
        ['equilibrado', 'Equilibrado', 80, 100, 'var(--petroleo)'],
    ];
    // Escala de IMC de 15 a 40: [etiqueta, desde, hasta, color]
    $imcEscala = [['Bajo', 15, 18.5, 'var(--cielo)'], ['Normal', 18.5, 25, 'var(--petroleo)'], ['Sobrepeso', 25, 30, 'var(--naranja)'], ['Obesidad', 30, 40, 'var(--cafe)']];
    $imcPos = max(0, min(100, ($r['imc']['valor'] - 15) / 25 * 100));
@endphp

<x-layout titulo="Tu reporte">
    <x-encabezado titulo="Tu reporte de bienestar"
                  :antetitulo="'Valoración del '.\Illuminate\Support\Carbon::parse($r['fecha'])->translatedFormat('j \d\e F \d\e Y')"
                  subtitulo="Resultado de tus respuestas en las 6 áreas de bienestar.">
        <x-slot:acciones>
            <x-boton variante="claro" icono="impresora" onclick="window.print()">Imprimir / PDF</x-boton>
            <x-boton :href="route('progreso')" variante="cta" icono="tendencia">Ver mi progreso</x-boton>
        </x-slot:acciones>
    </x-encabezado>

    <div class="contenedor -mt-16 space-y-6">
        {{-- Puntaje global y perfil --}}
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="tarjeta flex flex-col items-center justify-center text-center">
                <p class="etiqueta">Puntaje global</p>
                <div class="relative mt-4 size-44">
                    <svg viewBox="0 0 36 36" class="size-full -rotate-90" aria-hidden="true">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e2e8f0" stroke-width="3" />
                        <circle class="anillo" cx="18" cy="18" r="15.9" fill="none" stroke-width="3" stroke-linecap="round"
                                stroke="{{ $riesgo ? 'var(--cafe)' : 'var(--petroleo)' }}"
                                pathLength="100" stroke-dasharray="{{ $r['global'] }} 100" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-5xl font-bold tracking-tight text-slate-900">{{ $r['global'] }}</span>
                        <span class="text-sm text-slate-500">de 100</span>
                    </div>
                </div>
                <p class="mt-4 text-sm text-slate-500" id="comparacion" hidden></p>
            </div>

            <div @class(['tarjeta flex flex-col lg:col-span-2', 'border-cafe/30' => $riesgo])>
                <div class="flex items-start gap-4">
                    <span @class(['icono-caja', 'bg-cafe/10 text-cafe' => $riesgo])><x-icono nombre="medalla" /></span>
                    <div>
                        <p class="etiqueta">Tu perfil de salud</p>
                        <h2 @class(['mt-1 text-2xl font-semibold tracking-tight', 'text-cafe' => $riesgo, 'text-slate-900' => ! $riesgo])>{{ $r['perfil']['nombre'] }}</h2>
                    </div>
                </div>
                <p class="mt-4 leading-relaxed text-slate-600">{{ $r['perfil']['descripcion'] }}</p>

                {{-- Dónde cae el puntaje en la escala de perfiles --}}
                <div class="mt-auto pt-8" role="img" aria-label="Escala de perfiles: tu puntaje {{ $r['global'] }} está en {{ $r['perfil']['nombre'] }}">
                    <div class="relative">
                        <div class="flex h-2.5 gap-0.5 overflow-hidden rounded-full">
                            @foreach ($escala as [$k, , $desde, $hasta, $color])
                                <span style="width: {{ $hasta - $desde }}%; background: {{ $color }}" @class(['opacity-30' => $k !== $clave])></span>
                            @endforeach
                        </div>
                        <span class="absolute top-1/2 size-4 -translate-x-1/2 -translate-y-1/2 rounded-full border-[3px] border-white bg-slate-900 shadow" style="left: {{ $r['global'] }}%"></span>
                    </div>
                    <div class="mt-2 flex text-[11px] text-slate-500">
                        @foreach ($escala as [$k, $nombre, $desde, $hasta])
                            <span style="width: {{ $hasta - $desde }}%" @class(['font-semibold text-slate-900' => $k === $clave])>{{ $nombre }}<span class="hidden sm:inline"> · {{ $desde }}+</span></span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- IMC, fortaleza y foco --}}
        <div class="grid gap-6 md:grid-cols-3">
            <div class="tarjeta">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Índice de masa corporal</p>
                    <span class="flex size-8 items-center justify-center rounded-lg bg-petroleo/10 text-petroleo"><x-icono nombre="balanza" class="size-4" /></span>
                </div>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($r['imc']['valor'], 1, ',', '') }}
                    <span class="text-base font-medium text-slate-500">{{ $r['imc']['categoria'] }}</span></p>
                <div class="relative mt-5">
                    <div class="flex h-1.5 gap-0.5 overflow-hidden rounded-full">
                        @foreach ($imcEscala as [$nombre, $desde, $hasta, $color])
                            <span style="width: {{ ($hasta - $desde) * 4 }}%; background: {{ $color }}" @class(['opacity-30' => $nombre !== ($r['imc']['categoria'] === 'Bajo peso' ? 'Bajo' : $r['imc']['categoria'])])></span>
                        @endforeach
                    </div>
                    <span class="absolute top-1/2 size-3.5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-slate-900 shadow" style="left: {{ $imcPos }}%"></span>
                </div>
                <div class="mt-2 flex text-[11px] text-slate-500">
                    @foreach ($imcEscala as [$nombre, $desde, $hasta])
                        <span style="width: {{ ($hasta - $desde) * 4 }}%" class="truncate">{{ $nombre }}</span>
                    @endforeach
                </div>
            </div>

            @foreach ([[$fuerte, 'Tu fortaleza', 'Tu área con mejor puntaje. ¡Mantenla!'], [$foco, 'Tu foco', 'El área donde un cambio pequeño suma más.']] as [$area, $titulo, $texto])
                <div class="tarjeta">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">{{ $titulo }}</p>
                        <span @class(['flex size-8 items-center justify-center rounded-lg', 'bg-petroleo/10 text-petroleo' => $loop->first, 'bg-naranja/10 text-naranja' => $loop->last])>
                            <x-icono :nombre="WellnessScoreService::AREAS[$area][2]" class="size-4" />
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $r['areas'][$area] }}
                        <span class="text-base font-medium text-slate-500">{{ $etiquetas[$area] }}</span></p>
                    <p class="mt-2 text-sm text-slate-500">{{ $texto }}</p>
                </div>
            @endforeach
        </div>

        {{-- Radar y detalle por área --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="tarjeta">
                <h2 class="subtitulo">Tu perfil en 6 áreas</h2>
                <p class="mt-1 text-sm text-slate-500" id="radar-nota">Cuanto más amplia el área, mejor el equilibrio.</p>
                <div class="relative mt-4 h-80"><canvas id="radar" role="img" aria-label="Gráfico radar con el puntaje de cada área"></canvas></div>
            </div>

            <div class="tarjeta">
                <h2 class="subtitulo">Puntaje por área</h2>
                <div class="mt-6 space-y-5">
                    @foreach ($etiquetas as $k => $etiqueta)
                        <x-barra-progreso :etiqueta="$etiqueta" :icono="WellnessScoreService::AREAS[$k][2]" :valor="$r['areas'][$k]" :color="$tono($r['areas'][$k])" />
                    @endforeach
                </div>
                <div class="mt-6 flex flex-wrap gap-x-4 gap-y-1 border-t border-slate-100 pt-4 text-xs text-slate-500">
                    @foreach ([['var(--petroleo)', '70 o más: bueno'], ['var(--cielo)', '40 a 69: mejorable'], ['var(--cafe)', 'menos de 40: prioridad']] as [$color, $texto])
                        <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full" style="background: {{ $color }}"></span>{{ $texto }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Recomendaciones --}}
        <div class="tarjeta grid gap-8 lg:grid-cols-2">
            <div>
                <div class="flex items-center gap-3">
                    <span class="icono-caja bg-naranja/10 text-naranja"><x-icono nombre="foco" /></span>
                    <h2 class="subtitulo text-lg">Recomendaciones para ti</h2>
                </div>
                <ul class="mt-5 space-y-3">
                    @foreach ($r['recomendaciones'] as $texto)
                        <li class="flex gap-3 rounded-xl bg-fondo p-4 text-sm leading-relaxed text-slate-700">
                            <x-icono nombre="check" class="mt-0.5 size-4 shrink-0 text-petroleo" />{{ $texto }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <span class="icono-caja"><x-icono nombre="objetivo" /></span>
                    <h2 class="subtitulo text-lg">Plan para tu perfil</h2>
                </div>
                <ol class="mt-5 space-y-3">
                    @foreach ($r['perfil']['recomendaciones'] as $texto)
                        <li class="flex gap-4 rounded-xl border border-slate-200 p-4 text-sm leading-relaxed text-slate-700">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-petroleo text-xs font-semibold text-white">{{ $loop->iteration }}</span>
                            {{ $texto }}
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        <p class="flex flex-col items-center justify-center gap-2 text-center text-sm text-slate-500 sm:flex-row print:hidden">
            <x-icono nombre="check" class="size-4 text-petroleo" /> Este resultado se guardó en el historial de este navegador.
            <a href="{{ route('valoracion') }}" class="font-medium text-petroleo underline underline-offset-4">Hacer otra valoración</a>
        </p>
    </div>

    <script type="application/json" id="datos-resultado">@json(['resultado' => $r, 'etiquetas' => $etiquetas])</script>
</x-layout>
