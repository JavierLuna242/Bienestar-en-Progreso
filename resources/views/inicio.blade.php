@use('App\Services\WellnessScoreService')
@php
    $descripciones = [
        'cardiovascular' => 'Resistencia, recuperación y frecuencia cardíaca.',
        'fuerza' => 'Flexiones, sentadillas y entrenamiento de fuerza.',
        'movilidad' => 'Rango de movimiento y dolores frecuentes.',
        'nutricion' => 'Frutas, verduras, agua y ultraprocesados.',
        'descanso' => 'Horas y calidad del sueño, descanso activo.',
        'mental' => 'Estrés, motivación y constancia.',
    ];
    $ejemplo = ['cardiovascular' => 82, 'fuerza' => 64, 'movilidad' => 71, 'nutricion' => 58, 'descanso' => 76, 'mental' => 69];
@endphp

<x-layout>
    {{-- Hero --}}
    <section class="banda overflow-hidden">
        <div class="contenedor grid items-center gap-12 py-16 sm:py-24 lg:grid-cols-2">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-medium text-cielo">
                    <x-icono nombre="escudo" class="size-3.5" /> Sin registro · tus datos no salen de tu navegador
                </p>
                <h1 class="mt-6 text-4xl font-bold leading-tight tracking-tight text-white sm:text-5xl">
                    Conoce tu punto de partida y <span class="text-cielo">mide tu progreso</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-white/70">
                    Una valoración de salud y ejercicio en 5 minutos: puntaje en 6 áreas, tu perfil de salud y recomendaciones concretas para mejorar.
                </p>
                <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                    <x-boton :href="route('valoracion')" variante="cta" icono="flecha" class="px-6 py-3.5 text-base">Iniciar valoración</x-boton>
                    <x-boton :href="route('progreso')" variante="claro" icono="tendencia" class="px-6 py-3.5 text-base">Ver mi progreso</x-boton>
                </div>
                <a href="#video" class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-white/70 hover:text-white">
                    <span class="flex size-7 items-center justify-center rounded-full bg-white/10"><x-icono nombre="play" class="size-3.5" /></span>
                    Mira cómo funciona en 3 minutos
                </a>
                <dl class="mt-12 grid max-w-md grid-cols-3 gap-6 border-t border-white/10 pt-8">
                    @foreach ([['5 min', 'de duración'], ['6 áreas', 'evaluadas'], ['100 %', 'privado']] as [$cifra, $texto])
                        <div>
                            <dt class="sr-only">{{ $texto }}</dt>
                            <dd class="text-2xl font-semibold text-white">{{ $cifra }}</dd>
                            <dd class="text-sm text-white/60">{{ $texto }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Vista previa ilustrativa del reporte --}}
            <div class="relative mx-auto w-full max-w-md lg:mr-0" aria-hidden="true">
                <div class="absolute -inset-6 rounded-[2rem] bg-cielo/10 blur-2xl"></div>
                <div class="relative rounded-3xl border border-white/10 bg-white p-6 text-slate-700 shadow-2xl shadow-black/30">
                    <div class="flex items-center justify-between">
                        <p class="etiqueta">Ejemplo de reporte</p>
                        <span class="chip bg-cielo/15 text-petroleo">Activo en desarrollo</span>
                    </div>
                    <div class="mt-5 flex items-center gap-5">
                        <div class="relative size-24 shrink-0">
                            <svg viewBox="0 0 36 36" class="size-full -rotate-90">
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e2e8f0" stroke-width="3.2" />
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--petroleo)" stroke-width="3.2" stroke-linecap="round" pathLength="100" stroke-dasharray="71 100" />
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-2xl font-bold text-slate-900">71</span>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Puntaje global</p>
                            <p class="mt-1 text-sm"><span class="font-semibold text-slate-900">+9 puntos</span> desde tu primera valoración</p>
                        </div>
                    </div>
                    <div class="mt-6 space-y-3">
                        @foreach ($ejemplo as $clave => $v)
                            <div class="flex items-center gap-3 text-xs">
                                <span class="w-24 shrink-0 text-slate-500">{{ WellnessScoreService::AREAS[$clave][0] }}</span>
                                <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100"><span class="block h-full rounded-full bg-petroleo" style="width: {{ $v }}%"></span></span>
                                <span class="w-6 text-right font-semibold tabular-nums text-slate-900">{{ $v }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Cómo funciona --}}
    <section id="video" class="contenedor scroll-mt-20 py-16 sm:py-20">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold text-naranja">Cómo funciona</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Tres pasos, sin cuentas ni contraseñas</h2>
            <p class="mt-3 text-slate-600">En este video te mostramos las funcionalidades de la web y cómo usarla paso a paso.</p>
        </div>
        {{-- #t=0.1 hace que Safari muestre el primer fotograma como portada --}}
        <video src="/video/bienestar.mp4#t=0.1" controls preload="metadata" playsinline
               class="mt-10 aspect-video w-full rounded-2xl border border-slate-200/80 bg-noche shadow-xl shadow-slate-900/10">
            Tu navegador no puede reproducir este video. <a href="/video/bienestar.mp4" class="underline">Descárgalo aquí</a>.
        </video>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @foreach ([
                ['portapapeles', 'Responde el test', '7 secciones breves sobre tu actividad física, alimentación, descanso y bienestar mental.'],
                ['reporte', 'Recibe tu reporte', 'Puntaje global y por área, IMC, perfil de salud y recomendaciones. Descárgalo en PDF.'],
                ['tendencia', 'Sigue tu evolución', 'Repite la valoración cada mes y compara tus resultados en el panel de progreso.'],
            ] as [$icono, $titulo, $texto])
                <div class="tarjeta">
                    <div class="flex items-center gap-3">
                        <span class="icono-caja"><x-icono :nombre="$icono" /></span>
                        <span class="text-sm font-semibold text-slate-400">Paso {{ $loop->iteration }}</span>
                    </div>
                    <h3 class="subtitulo mt-5">{{ $titulo }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $texto }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Áreas --}}
    <section class="border-y border-slate-200 bg-white py-16 sm:py-20">
        <div class="contenedor">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold text-naranja">Qué evaluamos</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Una visión completa de tu bienestar</h2>
                <p class="mt-3 text-slate-600">Cada área recibe un puntaje de 0 a 100. El puntaje global las pondera, con mayor peso para la salud cardiovascular.</p>
            </div>
            <div class="mt-10 grid gap-px overflow-hidden rounded-2xl border border-slate-200 bg-slate-200 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (WellnessScoreService::AREAS as $clave => [$etiqueta, $peso, $icono])
                    <div class="flex gap-4 bg-white p-6">
                        <span class="icono-caja"><x-icono :nombre="$icono" /></span>
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ $etiqueta }} <span class="ml-1 text-xs font-medium text-slate-400">{{ round($peso * 100) }} %</span></h3>
                            <p class="mt-1 text-sm text-slate-600">{{ $descripciones[$clave] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Llamada final --}}
    <section class="contenedor pt-16 sm:pt-20">
        <div class="banda flex flex-col items-start gap-6 rounded-3xl px-6 py-10 sm:px-10 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-white">¿Listo para conocer tu punto de partida?</h2>
                <p class="mt-2 text-white/70">Toma 5 minutos. Puedes repetirlo cuando quieras para medir tu avance.</p>
            </div>
            <x-boton :href="route('valoracion')" variante="cta" icono="flecha" class="shrink-0 px-6 py-3.5 text-base">Iniciar valoración</x-boton>
        </div>
    </section>
</x-layout>
