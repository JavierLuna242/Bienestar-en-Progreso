<?php

use App\Http\Controllers\ValoracionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'inicio')->name('inicio');
Route::view('/valoracion', 'valoracion')->name('valoracion');
Route::post('/valoracion', [ValoracionController::class, 'store'])->name('valoracion.store');
Route::get('/resultado', [ValoracionController::class, 'resultado'])->name('resultado');
Route::view('/progreso', 'progreso')->name('progreso');
