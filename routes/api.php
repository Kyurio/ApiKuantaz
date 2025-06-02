<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BeneficioController;


//Route::get('/beneficios-filtrados', [BeneficioController::class, 'index']);

Route::get('/beneficios-filtrados', [BeneficioController::class, 'obtenerBeneficiosFiltrados']);

Route::post('/beneficios-test', [BeneficioController::class, 'testBeneficios']);
