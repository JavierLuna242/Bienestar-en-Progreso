<?php

namespace Tests\Unit;

use App\Services\WellnessScoreService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WellnessScoreServiceTest extends TestCase
{
    private WellnessScoreService $servicio;

    /** Respuestas ideales en todas las preguntas. */
    private const IDEAL = [
        'edad' => 30, 'sexo' => 'masculino', 'peso' => 70, 'estatura' => 175,
        'dias_ejercicio' => 5, 'duracion' => 60, 'tipo_ejercicio' => 'mixto', 'esfuerzo' => 4,
        'escaleras' => 4, 'fc_reposo' => 55, 'recuperacion' => 4,
        'flexiones' => 5, 'sentadillas' => 5, 'movilidad' => 4, 'dolor' => 4,
        'frutas_verduras' => 4, 'agua' => 4, 'procesados' => 4,
        'horas_sueno' => 8, 'calidad_sueno' => 5, 'descanso_activo' => 2,
        'estres' => 5, 'motivacion' => 5, 'constancia' => 4,
    ];

    /** Peores respuestas posibles. */
    private const SEDENTARIO = [
        'edad' => 45, 'sexo' => 'masculino', 'peso' => 105, 'estatura' => 170,
        'dias_ejercicio' => 0, 'duracion' => 0, 'tipo_ejercicio' => 'cardio', 'esfuerzo' => 1,
        'escaleras' => 1, 'fc_reposo' => 95, 'recuperacion' => 1,
        'flexiones' => 1, 'sentadillas' => 1, 'movilidad' => 1, 'dolor' => 1,
        'frutas_verduras' => 1, 'agua' => 1, 'procesados' => 1,
        'horas_sueno' => 5, 'calidad_sueno' => 1, 'descanso_activo' => 0,
        'estres' => 1, 'motivacion' => 1, 'constancia' => 1,
    ];

    protected function setUp(): void
    {
        $this->servicio = new WellnessScoreService;
    }

    public function test_respuestas_ideales_dan_100_en_todo(): void
    {
        $r = $this->servicio->calcular(self::IDEAL);

        $this->assertSame(100, $r['global']);
        $this->assertSame(array_fill_keys(array_keys(WellnessScoreService::AREAS), 100), $r['areas']);
        $this->assertSame('Rendimiento equilibrado', $r['perfil']['nombre']);
        $this->assertCount(1, $r['recomendaciones']); // solo el mensaje de mantenimiento
        $this->assertSame(5, $r['dias_actividad']);
    }

    public function test_respuestas_sedentarias_dan_perfil_de_riesgo(): void
    {
        $r = $this->servicio->calcular(self::SEDENTARIO);

        $this->assertLessThan(40, $r['global']);
        $this->assertSame('riesgo', $r['perfil']['clave']);
        $this->assertSame(4, $r['areas']['cardiovascular']); // solo aporta el pulso: 20 * 2/10
        // IMC fuera de rango primero + 3 áreas más bajas
        $this->assertCount(4, $r['recomendaciones']);
        $this->assertStringContainsString('Obesidad', $r['recomendaciones'][0]);
    }

    public function test_puntaje_global_es_el_promedio_ponderado_de_las_areas(): void
    {
        $datos = ['flexiones' => 2, 'agua' => 2, 'estres' => 3, 'horas_sueno' => 6] + self::IDEAL;
        $r = $this->servicio->calcular($datos);

        $esperado = 0;
        foreach (WellnessScoreService::AREAS as $clave => [, $peso]) {
            $esperado += $r['areas'][$clave] * $peso;
        }
        $this->assertSame((int) round($esperado), $r['global']);
        $this->assertEqualsWithDelta(1.0, array_sum(array_column(WellnessScoreService::AREAS, 1)), 1e-9);
    }

    public function test_todas_las_areas_quedan_entre_0_y_100(): void
    {
        foreach ([self::IDEAL, self::SEDENTARIO, ['edad' => 70, 'dias_ejercicio' => 7, 'duracion' => 300, 'esfuerzo' => 5] + self::IDEAL] as $datos) {
            foreach ($this->servicio->calcular($datos)['areas'] as $area => $puntaje) {
                $this->assertGreaterThanOrEqual(0, $puntaje, $area);
                $this->assertLessThanOrEqual(100, $puntaje, $area);
            }
        }
    }

    #[DataProvider('imcs')]
    public function test_categoria_de_imc(float $peso, float $valor, string $categoria): void
    {
        // Con 100 cm de estatura, el IMC es igual al peso.
        $r = $this->servicio->calcular(['peso' => $peso, 'estatura' => 100] + self::IDEAL);

        $this->assertSame(['valor' => $valor, 'categoria' => $categoria], $r['imc']);
    }

    public static function imcs(): array
    {
        return [
            [18.4, 18.4, 'Bajo peso'],
            [18.5, 18.5, 'Normal'],
            [24.9, 24.9, 'Normal'],
            [25, 25.0, 'Sobrepeso'],
            [29.9, 29.9, 'Sobrepeso'],
            [30, 30.0, 'Obesidad'],
        ];
    }

    public function test_imc_redondea_a_un_decimal(): void
    {
        $this->assertSame(22.9, $this->servicio->calcular(self::IDEAL)['imc']['valor']); // 70 / 1.75²
    }

    #[DataProvider('perfiles')]
    public function test_perfil_segun_puntaje(int $global, string $nombre): void
    {
        $perfil = $this->servicio->perfil($global);

        $this->assertSame($nombre, $perfil['nombre']);
        $this->assertNotEmpty($perfil['descripcion']);
        $this->assertCount(3, $perfil['recomendaciones']);
    }

    public static function perfiles(): array
    {
        return [
            [0, 'Sedentario en riesgo'],
            [39, 'Sedentario en riesgo'],
            [40, 'En construcción'],
            [59, 'En construcción'],
            [60, 'Activo en desarrollo'],
            [79, 'Activo en desarrollo'],
            [80, 'Rendimiento equilibrado'],
            [100, 'Rendimiento equilibrado'],
        ];
    }

    public function test_pulso_desconocido_no_penaliza(): void
    {
        $r = $this->servicio->calcular(['fc_reposo' => null] + self::IDEAL);

        $this->assertSame(100, $r['areas']['cardiovascular']);
    }

    public function test_pulso_elevado_baja_el_area_cardiovascular(): void
    {
        $normal = $this->servicio->calcular(self::IDEAL)['areas']['cardiovascular'];
        $alto = $this->servicio->calcular(['fc_reposo' => 95] + self::IDEAL)['areas']['cardiovascular'];

        $this->assertSame(84, $alto); // (100*3 + 100*2.5 + 100*2.5 + 20*2) / 10
        $this->assertLessThan($normal, $alto);
    }

    public function test_pulso_se_ajusta_por_sexo(): void
    {
        $datos = ['fc_reposo' => 63] + self::IDEAL;
        $hombre = $this->servicio->calcular($datos)['areas']['cardiovascular'];
        $mujer = $this->servicio->calcular(['sexo' => 'femenino'] + $datos)['areas']['cardiovascular'];

        $this->assertGreaterThan($hombre, $mujer);
    }

    public function test_fuerza_considera_la_edad(): void
    {
        $datos = ['flexiones' => 3, 'sentadillas' => 3] + self::IDEAL;
        $joven = $this->servicio->calcular($datos)['areas']['fuerza'];
        $mayor = $this->servicio->calcular(['edad' => 55] + $datos)['areas']['fuerza'];

        $this->assertSame($joven + 10, $mayor);
    }

    public function test_el_tipo_de_ejercicio_orienta_el_area_que_mejora(): void
    {
        $poco = ['dias_ejercicio' => 2, 'duracion' => 30] + self::IDEAL;
        $cardio = $this->servicio->calcular(['tipo_ejercicio' => 'cardio'] + $poco)['areas'];
        $fuerza = $this->servicio->calcular(['tipo_ejercicio' => 'fuerza'] + $poco)['areas'];

        $this->assertGreaterThan($fuerza['cardiovascular'], $cardio['cardiovascular']);
        $this->assertGreaterThan($cardio['fuerza'], $fuerza['fuerza']);
    }

    public function test_recomendaciones_apuntan_a_las_areas_mas_bajas(): void
    {
        $r = $this->servicio->calcular(['horas_sueno' => 4, 'calidad_sueno' => 1, 'descanso_activo' => 0] + self::IDEAL);

        $this->assertLessThan(70, $r['areas']['descanso']);
        $this->assertCount(1, $r['recomendaciones']);
        $this->assertStringContainsString('dormir', $r['recomendaciones'][0]);
    }

    public function test_acepta_valores_como_texto_del_formulario(): void
    {
        $comoTexto = array_map(fn ($v) => (string) $v, self::IDEAL);

        $this->assertSame($this->servicio->calcular(self::IDEAL), $this->servicio->calcular($comoTexto));
    }
}
