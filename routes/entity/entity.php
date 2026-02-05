<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Entity\Controller\EntityController;

Route::prefix('entities')->group(function () {
    Route::get('obtenerEntidades', [EntityController::class, 'obtenerEntidades']);
    Route::post('crearEntidad', [EntityController::class, 'crearEntidad']);
    Route::get('obtenerEntidad/{id}', [EntityController::class, 'obtenerEntidad']);
    Route::put('modificarEntidad/{id}', [EntityController::class, 'modificarEntidad']);
    Route::delete('eliminarEntidad/{id}', [EntityController::class, 'eliminarEntidad']);
    Route::post('asignarPlan/{id}', [EntityController::class, 'asignarPlan']);
});
