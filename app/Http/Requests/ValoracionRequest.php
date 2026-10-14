<?php

namespace App\Http\Requests;

use App\Services\WellnessScoreService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValoracionRequest extends FormRequest
{
    public function rules(): array
    {
        $reglas = [
            'edad' => 'required|integer|between:14,100',
            'peso' => 'required|numeric|between:25,300',
            'estatura' => 'required|numeric|between:100,230',
            'dias_ejercicio' => 'required|integer|between:0,7',
            'duracion' => 'required|integer|between:0,300',
            'fc_reposo' => 'nullable|integer|between:35,140',
            'horas_sueno' => 'required|numeric|between:3,14',
            'descanso_activo' => 'required|integer|between:0,7',
        ];

        foreach (WellnessScoreService::OPCIONES as $campo => $opciones) {
            $reglas[$campo] = ['required', Rule::in(array_keys($opciones))];
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'required' => 'Responde la pregunta «:attribute».',
            'integer' => '«:attribute» debe ser un número entero.',
            'numeric' => '«:attribute» debe ser un número.',
            'between' => '«:attribute» debe estar entre :min y :max.',
            'in' => 'Elige una opción válida en «:attribute».',
        ];
    }

    public function attributes(): array
    {
        return [
            'edad' => 'Edad',
            'sexo' => 'Sexo',
            'peso' => 'Peso',
            'estatura' => 'Estatura',
            'dias_ejercicio' => 'Días de ejercicio',
            'duracion' => 'Duración promedio',
            'tipo_ejercicio' => 'Tipo de ejercicio',
            'esfuerzo' => 'Nivel de esfuerzo',
            'escaleras' => 'Cansancio al subir escaleras',
            'fc_reposo' => 'Frecuencia cardíaca en reposo',
            'recuperacion' => 'Recuperación',
            'flexiones' => 'Flexiones',
            'sentadillas' => 'Sentadillas',
            'movilidad' => 'Rango de movimiento',
            'dolor' => 'Dolores frecuentes',
            'frutas_verduras' => 'Frutas y verduras',
            'agua' => 'Agua diaria',
            'procesados' => 'Comida procesada',
            'horas_sueno' => 'Horas de sueño',
            'calidad_sueno' => 'Calidad del sueño',
            'descanso_activo' => 'Días de descanso activo',
            'estres' => 'Nivel de estrés',
            'motivacion' => 'Motivación',
            'constancia' => 'Constancia',
        ];
    }
}
