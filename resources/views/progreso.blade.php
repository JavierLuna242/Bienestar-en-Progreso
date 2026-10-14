@use('App\Services\WellnessScoreService')

<x-layout titulo="Mi progreso">
    <x-encabezado titulo="Tu progreso" antetitulo="Panel personal"
                  subtitulo="Evolución de tus valoraciones. Los datos se guardan solo en este navegador.">
        <x-slot:acciones>
            <x-boton :href="route('valoracion')" variante="cta" icono="flecha">Nueva valoración</x-boton>
        </x-slot:acciones>
    </x-encabezado>

    <div class="contenedor -mt-16">
        {{-- Estado vacío (visible hasta que JS encuentre historial) --}}
        <div id="vacio" class="tarjeta flex flex-col items-center px-6 py-16 text-center">
            <span class="flex size-16 items-center justify-center rounded-2xl bg-petroleo/10 text-petroleo"><x-icono nombre="tendencia" class="size-8" /></span>
            <h2 class="mt-6 text-xl font-semibold text-slate-900">Aún no tienes valoraciones</h2>
            <p class="mt-2 max-w-md text-slate-600">Haz tu primera valoración y aquí verás cómo evolucionan tu puntaje global y cada una de tus 6 áreas.</p>
            <x-boton :href="route('valoracion')" variante="cta" icono="flecha" class="mt-8">Iniciar valoración</x-boton>
        </div>

        <div id="con-datos" class="space-y-6" hidden>
            {{-- Indicadores --}}
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                <x-tarjeta-estadistica titulo="Última valoración" icono="reporte" data-stat="ultima" class="col-span-2 lg:col-span-1">
                    <div class="mt-3" data-perfil></div>
                </x-tarjeta-estadistica>
                <x-tarjeta-estadistica titulo="Promedio" icono="barras" data-stat="promedio" />
                <x-tarjeta-estadistica titulo="Mejor puntaje" icono="medalla" data-stat="mejor" />
                <x-tarjeta-estadistica titulo="Cambio total" icono="tendencia" data-stat="cambio" />
                <x-tarjeta-estadistica titulo="Días de actividad" icono="calendario" data-stat="dias">
                    <div class="mt-3 flex gap-1" data-dias aria-hidden="true"></div>
                </x-tarjeta-estadistica>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Evolución del puntaje global --}}
                <div class="tarjeta lg:col-span-2">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="subtitulo">Evolución del puntaje global</h2>
                            <p class="mt-1 text-sm text-slate-500" id="nota-evolucion">Las franjas de fondo indican el perfil que corresponde a cada puntaje.</p>
                        </div>
                    </div>
                    <div class="relative mt-6 h-80"><canvas id="evolucion" role="img" aria-label="Gráfico de líneas con la evolución del puntaje global"></canvas></div>
                </div>

                {{-- Áreas en la última valoración, ordenadas --}}
                <div class="tarjeta">
                    <h2 class="subtitulo">Tus áreas hoy</h2>
                    <p class="mt-1 text-sm text-slate-500">Última valoración, de mayor a menor.</p>
                    <div class="mt-6 flex flex-col gap-4" id="ranking">
                        @foreach (WellnessScoreService::AREAS as $clave => [$etiqueta, , $icono])
                            <div data-area="{{ $clave }}">
                                <div class="mb-1.5 flex items-center justify-between gap-2 text-sm">
                                    <span class="flex items-center gap-2 font-medium text-slate-700"><x-icono :nombre="$icono" class="size-4 text-slate-400" />{{ $etiqueta }}<span class="chip bg-naranja/10 text-[#b5501f]" data-foco hidden>Tu foco</span></span>
                                    <span class="font-semibold tabular-nums text-slate-900" data-texto></span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-200/70"><div class="barra h-full rounded-full bg-petroleo" data-barra style="width: 0"></div></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Evolución por área (múltiplos pequeños) --}}
            <div>
                <h2 class="subtitulo">Evolución por área</h2>
                <p class="mt-1 text-sm text-slate-500">Puntaje actual, cambio desde tu primera valoración y tendencia.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach (WellnessScoreService::AREAS as $clave => [$etiqueta, , $icono])
                        <div class="tarjeta" data-mini="{{ $clave }}">
                            <div class="flex items-center gap-3">
                                <span class="flex size-8 items-center justify-center rounded-lg bg-petroleo/10 text-petroleo"><x-icono :nombre="$icono" class="size-4" /></span>
                                <span class="font-medium text-slate-700">{{ $etiqueta }}</span>
                            </div>
                            <div class="mt-4 flex items-end justify-between gap-4">
                                <div>
                                    <p class="text-3xl font-semibold tracking-tight text-slate-900" data-valor>—</p>
                                    <div class="mt-1" data-delta></div>
                                </div>
                                <div class="w-1/2 max-w-40" data-spark></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Historial --}}
            <div class="tarjeta p-0 sm:p-0">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 class="subtitulo">Historial de valoraciones</h2>
                        <p class="mt-1 text-sm text-slate-500" id="conteo"></p>
                    </div>
                    <x-boton variante="peligro" icono="papelera" id="borrar-todo">Borrar todo el historial</x-boton>
                </div>
                <div class="relative overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-fondo text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold sm:px-6">Fecha</th>
                                <th class="px-3 py-3 font-semibold">Puntaje</th>
                                <th class="px-3 py-3 font-semibold">Perfil</th>
                                <th class="px-3 py-3 font-semibold">Cambio</th>
                                <th class="hidden px-3 py-3 font-semibold sm:table-cell">IMC</th>
                                <th class="px-5 py-3 sm:px-6"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody id="filas" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="datos-progreso">@json(['etiquetas' => array_map(fn ($a) => $a[0], WellnessScoreService::AREAS)])</script>
</x-layout>
