<?php

namespace Tests\Feature;

use Tests\TestCase;

class ValoracionTest extends TestCase
{
    private array $respuestas = [
        'edad' => 30, 'sexo' => 'femenino', 'peso' => 62.5, 'estatura' => 165,
        'dias_ejercicio' => 3, 'duracion' => 45, 'tipo_ejercicio' => 'mixto', 'esfuerzo' => 3,
        'escaleras' => 3, 'fc_reposo' => '', 'recuperacion' => 3,
        'flexiones' => 3, 'sentadillas' => 3, 'movilidad' => 3, 'dolor' => 3,
        'frutas_verduras' => 3, 'agua' => 3, 'procesados' => 3,
        'horas_sueno' => 7.5, 'calidad_sueno' => 4, 'descanso_activo' => 2,
        'estres' => 3, 'motivacion' => 4, 'constancia' => 3,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_paginas_publicas(): void
    {
        $this->get('/')->assertOk()->assertSee('Iniciar valoración');
        $this->get('/valoracion')->assertOk()->assertSee('Test de bienestar');
        $this->get('/progreso')->assertOk()->assertSee('Aún no tienes valoraciones');
    }

    public function test_flujo_completo_hasta_el_reporte(): void
    {
        $this->post('/valoracion', $this->respuestas)
            ->assertRedirect('/resultado')
            ->assertSessionHas('resultado.perfil.nombre');

        $this->get('/resultado')->assertOk()->assertSee('Tu reporte de bienestar')->assertSee('datos-resultado', false);
        $this->get('/resultado')->assertOk(); // recargar conserva el resultado
    }

    public function test_resultado_sin_valoracion_redirige_al_test(): void
    {
        $this->get('/resultado')->assertRedirect('/valoracion');
    }

    public function test_validacion_en_espanol(): void
    {
        $this->post('/valoracion', ['edad' => 5, 'tipo_ejercicio' => 'yoga'] + $this->respuestas)
            ->assertSessionHasErrors([
                'edad' => '«Edad» debe estar entre 14 y 100.',
                'tipo_ejercicio' => 'Elige una opción válida en «Tipo de ejercicio».',
            ]);

        $this->post('/valoracion', [])->assertSessionHasErrors(['peso' => 'Responde la pregunta «Peso».']);
    }
}
