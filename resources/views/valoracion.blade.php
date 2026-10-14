@use('App\Services\WellnessScoreService')
@php
    // [titulo, icono, campos]; cada campo: [nombre, pregunta, atributos del input numérico (null = opciones de OPCIONES), ayuda]
    $secciones = [
        ['Datos básicos', 'usuario', [
            ['edad', '¿Cuántos años tienes?', ['min' => 14, 'max' => 100, 'step' => 1, 'sufijo' => 'años']],
            ['sexo', 'Sexo', null],
            ['peso', 'Peso', ['min' => 25, 'max' => 300, 'step' => 0.1, 'sufijo' => 'kg']],
            ['estatura', 'Estatura', ['min' => 100, 'max' => 230, 'step' => 1, 'sufijo' => 'cm']],
        ]],
        ['Actividad física', 'rayo', [
            ['dias_ejercicio', '¿Cuántos días a la semana haces ejercicio?', ['min' => 0, 'max' => 7, 'step' => 1, 'sufijo' => 'días']],
            ['duracion', 'Duración promedio de cada sesión', ['min' => 0, 'max' => 300, 'step' => 5, 'sufijo' => 'min']],
            ['tipo_ejercicio', 'Tipo de ejercicio que prefieres', null],
            ['esfuerzo', '¿Qué tan intenso sientes tu ejercicio?', null],
        ]],
        ['Cardiovascular y resistencia', 'pulso', [
            ['escaleras', 'Al subir 3 pisos por escaleras…', null],
            ['fc_reposo', 'Frecuencia cardíaca en reposo (opcional)', ['min' => 35, 'max' => 140, 'step' => 1, 'sufijo' => 'lpm', 'opcional' => true], 'Si la conoces: mídela al despertar, antes de levantarte.'],
            ['recuperacion', 'Después de un esfuerzo intenso, ¿cuánto tardas en recuperar el aliento?', null],
        ]],
        ['Fuerza y movilidad', 'pesa', [
            ['flexiones', '¿Cuántas flexiones seguidas puedes hacer?', null, 'Valen apoyando las rodillas.'],
            ['sentadillas', '¿Cuántas sentadillas seguidas puedes hacer?', null],
            ['movilidad', 'De pie y con las piernas rectas, al inclinarte hacia adelante…', null],
            ['dolor', '¿Con qué frecuencia tienes dolores articulares o musculares?', null],
        ]],
        ['Nutrición e hidratación', 'manzana', [
            ['frutas_verduras', 'Porciones de frutas y verduras al día', null],
            ['agua', '¿Cuánta agua bebes al día?', null],
            ['procesados', 'Comida procesada o rápida a la semana', null],
        ]],
        ['Descanso y recuperación', 'luna', [
            ['horas_sueno', 'Horas de sueño por noche', ['min' => 3, 'max' => 14, 'step' => 0.5, 'sufijo' => 'h']],
            ['calidad_sueno', 'Calidad de tu sueño', null],
            ['descanso_activo', 'Días de descanso activo a la semana', ['min' => 0, 'max' => 7, 'step' => 1, 'sufijo' => 'días'], 'Caminar, estirar o actividades suaves en días sin entrenamiento.'],
        ]],
        ['Bienestar mental', 'mente', [
            ['estres', 'Tu nivel de estrés habitual', null],
            ['motivacion', 'Tu motivación para cuidarte', null],
            ['constancia', '¿Qué tan seguido cumples lo que te propones (ejercicio, dieta, sueño)?', null],
        ]],
    ];
@endphp

<x-layout titulo="Valoración">
    <x-encabezado titulo="Test de bienestar" antetitulo="Valoración de salud y ejercicio"
                  subtitulo="Responde según tu última semana típica. No hay respuestas buenas ni malas: lo importante es que reflejen tu realidad.">
        <div class="contenedor mt-6 flex flex-wrap gap-2 text-xs text-white/70">
            @foreach ([['portapapeles', count($secciones).' secciones'], ['reloj', 'Unos 5 minutos'], ['escudo', 'Privado: sin registro']] as [$icono, $texto])
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/5 px-3 py-1"><x-icono :nombre="$icono" class="size-3.5" />{{ $texto }}</span>
            @endforeach
        </div>
    </x-encabezado>

    <form method="post" action="{{ route('valoracion.store') }}" data-valoracion
          class="contenedor -mt-16 grid gap-6 lg:grid-cols-[16rem_1fr] lg:items-start">
        @csrf

        {{-- Índice y avance: barra fija en móvil, panel lateral en escritorio --}}
        <aside class="sticky top-[4.5rem] z-20 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm backdrop-blur lg:top-24 lg:p-5">
            <x-barra-progreso etiqueta="Tu avance" sufijo="%" data-avance />
            <ol class="mt-5 hidden space-y-1 text-sm lg:block">
                @foreach ($secciones as $i => [$titulo, $icono])
                    <li>
                        <a href="#seccion-{{ $i }}" data-paso="{{ $i }}" class="group flex items-center gap-3 rounded-lg px-2 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                            <span class="flex size-6 shrink-0 items-center justify-center rounded-full border border-slate-300 text-xs font-semibold text-slate-500 group-data-[completo]:border-petroleo group-data-[completo]:bg-petroleo group-data-[completo]:text-white">{{ $i + 1 }}</span>
                            {{ $titulo }}
                        </a>
                    </li>
                @endforeach
            </ol>
        </aside>

        <div class="space-y-6">
            @if (session('aviso'))
                <p class="tarjeta flex items-center gap-3 border-cielo/40 py-4 text-sm text-petroleo"><x-icono nombre="foco" class="size-5 shrink-0" />{{ session('aviso') }}</p>
            @endif
            @if ($errors->any())
                <p class="tarjeta border-cafe/30 bg-[#fbf6f3] py-4 text-sm text-cafe" role="alert">
                    Revisa los campos marcados: hay {{ $errors->count() }} {{ $errors->count() === 1 ? 'respuesta' : 'respuestas' }} por corregir.
                </p>
            @endif

            @foreach ($secciones as $i => [$titulo, $icono, $campos])
                <section id="seccion-{{ $i }}" data-seccion="{{ $i }}" class="tarjeta scroll-mt-40 p-0 sm:p-0 lg:scroll-mt-24">
                    <header class="flex items-center gap-4 border-b border-slate-100 px-5 py-5 sm:px-7">
                        <span class="icono-caja"><x-icono :nombre="$icono" /></span>
                        <div>
                            <p class="etiqueta">Paso {{ $i + 1 }} de {{ count($secciones) }}</p>
                            <h2 class="subtitulo mt-0.5 text-lg">{{ $titulo }}</h2>
                        </div>
                    </header>

                    <div class="divide-y divide-slate-100">
                        @foreach ($campos as $c)
                            @php [$nombre, $pregunta, $num] = $c; $ayuda = $c[3] ?? null; @endphp
                            <div class="px-5 py-5 sm:px-7">
                                @if ($num)
                                    <label for="{{ $nombre }}" class="mb-2.5 block font-medium text-slate-900">{{ $pregunta }}</label>
                                    <div class="relative sm:max-w-xs">
                                        <input id="{{ $nombre }}" name="{{ $nombre }}" type="number" inputmode="decimal"
                                               min="{{ $num['min'] }}" max="{{ $num['max'] }}" step="{{ $num['step'] }}"
                                               value="{{ old($nombre) }}" @required(empty($num['opcional']))
                                               @error($nombre) aria-invalid="true" @enderror
                                               @class(['campo pr-16', 'border-cafe' => $errors->has($nombre)])>
                                        <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm font-medium text-slate-400">{{ $num['sufijo'] }}</span>
                                    </div>
                                @else
                                    <fieldset>
                                        <legend class="mb-2.5 font-medium text-slate-900">{{ $pregunta }}</legend>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach (WellnessScoreService::OPCIONES[$nombre] as $valor => $texto)
                                                <label class="opcion">
                                                    <input type="radio" name="{{ $nombre }}" value="{{ $valor }}" class="sr-only" required
                                                           @checked((string) old($nombre) === (string) $valor)>
                                                    {{ $texto }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                @endif
                                @if ($ayuda)
                                    <p class="mt-2 text-sm text-slate-500">{{ $ayuda }}</p>
                                @endif
                                @error($nombre)
                                    <p class="mt-2 text-sm font-medium text-cafe">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="tarjeta flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="flex items-center gap-2 text-sm text-slate-600"><x-icono nombre="escudo" class="size-4 text-petroleo" /> Tus respuestas no se guardan en ningún servidor.</p>
                <x-boton type="submit" variante="cta" icono="flecha" class="px-6 py-3.5 text-base">Ver mis resultados</x-boton>
            </div>
        </div>
    </form>
</x-layout>
