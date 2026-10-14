<?php

namespace App\Services;

/**
 * Calcula la valoración de bienestar a partir de las respuestas del test.
 * Toda la lógica de puntuación vive aquí; los controladores solo la invocan.
 */
class WellnessScoreService
{
    /** Áreas evaluadas: [etiqueta, peso en el puntaje global, icono de la interfaz]. Los pesos suman 1. */
    public const AREAS = [
        'cardiovascular' => ['Cardiovascular', 0.25, 'pulso'],
        'fuerza' => ['Fuerza', 0.15, 'pesa'],
        'movilidad' => ['Movilidad', 0.15, 'movilidad'],
        'nutricion' => ['Nutrición', 0.15, 'manzana'],
        'descanso' => ['Descanso', 0.15, 'luna'],
        'mental' => ['Bienestar mental', 0.15, 'mente'],
    ];

    /**
     * Opciones de las preguntas de selección (las usan la vista, la validación y el cálculo).
     * En las escalas numéricas el valor 1 es la peor respuesta y el último, la mejor.
     */
    public const OPCIONES = [
        'sexo' => ['femenino' => 'Femenino', 'masculino' => 'Masculino'],
        'tipo_ejercicio' => ['fuerza' => 'Fuerza', 'cardio' => 'Cardio', 'flexibilidad' => 'Flexibilidad', 'mixto' => 'Mixto'],
        'esfuerzo' => [1 => 'Muy suave', 2 => 'Suave', 3 => 'Moderado', 4 => 'Intenso', 5 => 'Máximo'],
        'escaleras' => [1 => 'Tengo que parar', 2 => 'Me agito bastante', 3 => 'Algo de cansancio', 4 => 'Sin problema'],
        'recuperacion' => [1 => 'Más de 10 min', 2 => '5 a 10 min', 3 => '2 a 5 min', 4 => 'Menos de 2 min'],
        'flexiones' => [1 => 'Ninguna', 2 => '1 a 5', 3 => '6 a 15', 4 => '16 a 30', 5 => 'Más de 30'],
        'sentadillas' => [1 => 'Ninguna', 2 => '1 a 10', 3 => '11 a 25', 4 => '26 a 40', 5 => 'Más de 40'],
        'movilidad' => [1 => 'No paso de las rodillas', 2 => 'Llego a la espinilla', 3 => 'Toco los tobillos', 4 => 'Toco el suelo'],
        'dolor' => [1 => 'A diario', 2 => 'Varias veces por semana', 3 => 'Ocasionalmente', 4 => 'Casi nunca'],
        'frutas_verduras' => [1 => '0 a 1', 2 => '2 a 3', 3 => '4 a 5', 4 => 'Más de 5'],
        'agua' => [1 => 'Menos de 1 L', 2 => '1 a 1,5 L', 3 => '1,5 a 2 L', 4 => 'Más de 2 L'],
        'procesados' => [1 => 'A diario', 2 => '4 a 6 veces', 3 => '1 a 3 veces', 4 => 'Rara vez'],
        'calidad_sueno' => [1 => 'Muy mala', 2 => 'Mala', 3 => 'Regular', 4 => 'Buena', 5 => 'Excelente'],
        'estres' => [1 => 'Muy alto', 2 => 'Alto', 3 => 'Moderado', 4 => 'Bajo', 5 => 'Muy bajo'],
        'motivacion' => [1 => 'Muy baja', 2 => 'Baja', 3 => 'Media', 4 => 'Alta', 5 => 'Muy alta'],
        'constancia' => [1 => 'Casi nunca', 2 => 'A veces', 3 => 'Casi siempre', 4 => 'Siempre'],
    ];

    /** Perfiles por puntaje global mínimo (de mayor a menor). */
    private const PERFILES = [
        80 => [
            'clave' => 'equilibrado',
            'nombre' => 'Rendimiento equilibrado',
            'descripcion' => 'Tus hábitos están bien compensados entre ejercicio, alimentación, descanso y mente. El reto ahora es mantener y afinar.',
            'recomendaciones' => [
                'Varía tus estímulos cada 4 a 6 semanas (intervalos, nuevas rutinas de fuerza o un deporte distinto) para seguir progresando.',
                'Programa una semana de descarga cada 2 meses, bajando el volumen a la mitad para recuperarte por completo.',
                'Ponte una meta medible a 3 meses, como una carrera de 10 km o mejorar tu marca de sentadillas.',
            ],
        ],
        60 => [
            'clave' => 'activo',
            'nombre' => 'Activo en desarrollo',
            'descripcion' => 'Ya tienes una base sólida de actividad. Con algunos ajustes en tus áreas más bajas puedes dar un salto importante.',
            'recomendaciones' => [
                'Alcanza al menos 150 minutos semanales de cardio moderado repartidos en 3 a 5 días.',
                'Incluye 2 sesiones de fuerza a la semana que trabajen piernas, empuje, tracción y abdomen.',
                'Dedica 10 minutos al final de cada sesión a estirar y a ejercicios de movilidad.',
            ],
        ],
        40 => [
            'clave' => 'construccion',
            'nombre' => 'En construcción',
            'descripcion' => 'Hay hábitos saludables en marcha, pero todavía de forma irregular. La constancia será tu mejor aliada.',
            'recomendaciones' => [
                'Fija 3 días y horarios concretos de ejercicio en tu agenda y trátalos como citas.',
                'Empieza con sesiones de 20 a 30 minutos y súmales 5 minutos cada semana.',
                'Añade una porción de verdura a cada comida principal y lleva contigo una botella de agua.',
            ],
        ],
        0 => [
            'clave' => 'riesgo',
            'nombre' => 'Sedentario en riesgo',
            'descripcion' => 'Tu nivel de actividad y tus hábitos actuales aumentan el riesgo para tu salud. La buena noticia: los primeros cambios son los que más se notan.',
            'recomendaciones' => [
                'Camina 10 minutos después de cada comida; son 30 minutos diarios sin necesidad de ir a un gimnasio.',
                'Levántate y muévete 2 o 3 minutos por cada hora que pases sentado.',
                'Consulta con un profesional de la salud antes de empezar un programa de ejercicio intenso.',
            ],
        ],
    ];

    /** Consejo personalizado para cada área con puntaje bajo. */
    private const CONSEJOS = [
        'cardiovascular' => 'Mejora tu resistencia con caminatas rápidas, bici o natación: empieza con 3 sesiones de 20 minutos por semana.',
        'fuerza' => 'Gana fuerza con ejercicios con tu propio peso (sentadillas, flexiones apoyando las rodillas, puente de glúteo) 2 veces por semana.',
        'movilidad' => 'Dedica 10 minutos diarios a estirar caderas, espalda y hombros; si el dolor persiste, consulta a un fisioterapeuta.',
        'nutricion' => 'Llena la mitad de tu plato con frutas y verduras, bebe agua a lo largo del día y reduce los ultraprocesados.',
        'descanso' => 'Procura dormir de 7 a 9 horas con horarios regulares y evita pantallas 30 minutos antes de acostarte.',
        'mental' => 'Reserva 10 minutos al día para respirar, caminar o desconectar, y anota pequeñas metas semanales para sostener tu motivación.',
    ];

    /**
     * @param  array<string, mixed>  $d  Respuestas validadas del formulario.
     * @return array{global: int, areas: array<string, int>, imc: array{valor: float, categoria: string}, perfil: array, recomendaciones: list<string>, dias_actividad: int}
     */
    public function calcular(array $d): array
    {
        // Minutos semanales ajustados por el esfuerzo percibido ("intenso" = minutos tal cual).
        $minutos = $d['dias_ejercicio'] * $d['duracion'] * (0.6 + 0.1 * $d['esfuerzo']);
        // Qué tanto aporta el tipo de ejercicio a cada área, hasta una meta semanal de minutos.
        $volumen = fn (array $aporte, int $meta) => min(100, $minutos * $aporte[$d['tipo_ejercicio']] / $meta * 100);

        $areas = [
            'cardiovascular' => $this->ponderar([
                [$this->escala($d, 'escaleras'), 3],
                [$this->escala($d, 'recuperacion'), 2.5],
                [$volumen(['cardio' => 1, 'mixto' => .8, 'fuerza' => .5, 'flexibilidad' => .3], 150), 2.5],
                [$this->pulso($d), 2], // si no conoce su pulso, se reparte el peso entre el resto
            ]),
            'fuerza' => min(100, $this->ponderar([
                [$this->escala($d, 'flexiones'), 4],
                [$this->escala($d, 'sentadillas'), 4],
                [$volumen(['fuerza' => 1, 'mixto' => .7, 'cardio' => .3, 'flexibilidad' => .3], 90), 2],
            ]) + ($d['edad'] >= 50 ? 10 : 0)), // las marcas esperadas bajan con la edad
            'movilidad' => $this->ponderar([
                [$this->escala($d, 'movilidad'), 5],
                [$this->escala($d, 'dolor'), 3],
                [$volumen(['flexibilidad' => 1, 'mixto' => .6, 'fuerza' => .3, 'cardio' => .2], 60), 2],
            ]),
            'nutricion' => $this->ponderar([
                [$this->escala($d, 'frutas_verduras'), 4],
                [$this->escala($d, 'agua'), 3],
                [$this->escala($d, 'procesados'), 3],
            ]),
            'descanso' => $this->ponderar([
                [match (true) {
                    $d['horas_sueno'] >= 7 && $d['horas_sueno'] <= 9 => 100,
                    $d['horas_sueno'] >= 6 && $d['horas_sueno'] <= 10 => 65,
                    default => 30,
                }, 4],
                [$this->escala($d, 'calidad_sueno'), 4],
                [match (true) {
                    $d['descanso_activo'] == 0 => 40,
                    $d['descanso_activo'] <= 3 => 100,
                    default => 75,
                }, 2],
            ]),
            'mental' => $this->ponderar([
                [$this->escala($d, 'estres'), 4],
                [$this->escala($d, 'motivacion'), 3],
                [$this->escala($d, 'constancia'), 3],
            ]),
        ];
        $areas = array_map(fn ($v) => (int) round($v), $areas);

        $global = 0;
        foreach (self::AREAS as $clave => [, $peso]) {
            $global += $areas[$clave] * $peso;
        }
        $global = (int) round($global);

        $imc = round($d['peso'] / (($d['estatura'] / 100) ** 2), 1);
        $categoria = match (true) {
            $imc < 18.5 => 'Bajo peso',
            $imc < 25 => 'Normal',
            $imc < 30 => 'Sobrepeso',
            default => 'Obesidad',
        };

        return [
            'global' => $global,
            'areas' => $areas,
            'imc' => ['valor' => $imc, 'categoria' => $categoria],
            'perfil' => $this->perfil($global),
            'recomendaciones' => $this->recomendaciones($areas, $categoria),
            'dias_actividad' => (int) $d['dias_ejercicio'],
        ];
    }

    public function perfil(int $global): array
    {
        foreach (self::PERFILES as $minimo => $perfil) {
            if ($global >= $minimo) {
                return $perfil;
            }
        }

        return self::PERFILES[0];
    }

    /** Hasta 3 consejos para las áreas más bajas (< 70), más uno de IMC si está fuera de rango. */
    private function recomendaciones(array $areas, string $categoriaImc): array
    {
        asort($areas);
        $consejos = [];
        if ($categoriaImc !== 'Normal') {
            $consejos[] = "Tu IMC indica «{$categoriaImc}»: combina actividad regular con una alimentación equilibrada y, si puedes, pide orientación a un nutricionista.";
        }
        foreach (array_slice(array_filter($areas, fn ($v) => $v < 70), 0, 3, true) as $clave => $v) {
            $consejos[] = self::CONSEJOS[$clave];
        }

        return $consejos ?: ['Todas tus áreas están en buen nivel: mantén tu rutina y repite la valoración en un mes para seguir tu progreso.'];
    }

    /** Convierte una respuesta de escala (1 = peor) en un puntaje de 0 a 100. */
    private function escala(array $d, string $campo): float
    {
        return ($d[$campo] - 1) / (count(self::OPCIONES[$campo]) - 1) * 100;
    }

    private function pulso(array $d): ?int
    {
        if (empty($d['fc_reposo'])) {
            return null;
        }
        // En mujeres el pulso en reposo suele ser unos 3 lpm más alto.
        $fc = $d['fc_reposo'] - ($d['sexo'] === 'femenino' ? 3 : 0);

        return match (true) {
            $fc <= 60 => 100,
            $fc <= 70 => 85,
            $fc <= 80 => 65,
            $fc <= 90 => 40,
            default => 20,
        };
    }

    /** Promedio ponderado de pares [puntaje, peso], ignorando puntajes nulos. */
    private function ponderar(array $partes): float
    {
        $partes = array_filter($partes, fn ($p) => $p[0] !== null);

        return array_sum(array_map(fn ($p) => $p[0] * $p[1], $partes)) / array_sum(array_column($partes, 1));
    }
}
