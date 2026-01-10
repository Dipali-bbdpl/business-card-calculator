<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalculatorController;

Route::get('/', function () {
    return redirect()->route('calculator.index');
});

Route::get('/calculator/{slug?}', [CalculatorController::class, 'index'])->name('calculator.index');
Route::post('/calculate-price', [CalculatorController::class, 'calculate'])->name('calculate.price');