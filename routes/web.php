<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RelatorioController;

Route::get('/consumo', [RelatorioController::class, 'consumo']);
Route::get('/compras', [RelatorioController::class, 'compras']);
