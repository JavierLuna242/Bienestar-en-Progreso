<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValoracionRequest;
use App\Services\WellnessScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ValoracionController
{
    public function store(ValoracionRequest $request, WellnessScoreService $servicio)
    {
        $resultado = $servicio->calcular($request->validated()) + [
            'id' => (string) Str::uuid(), // evita duplicados en el historial del navegador al recargar
            'fecha' => now()->toIso8601String(),
        ];

        return redirect()->route('resultado')->with('resultado', $resultado);
    }

    public function resultado(Request $request)
    {
        $resultado = $request->session()->get('resultado');

        if (! $resultado) {
            return redirect()->route('valoracion')->with('aviso', 'Completa la valoración para ver tu reporte.');
        }

        $request->session()->keep('resultado'); // permite recargar el reporte sin perderlo

        return view('resultado', compact('resultado'));
    }
}
