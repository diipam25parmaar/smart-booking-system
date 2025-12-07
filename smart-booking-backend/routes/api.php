<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\WorkingTimeRuleController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['ok' => true, 'time' => now()->toDateTimeString()]);
})->name('health');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');

Route::get('/available-slots', AvailabilityController::class)->name('availability.index');

Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');

Route::get('/working-time-rules', [WorkingTimeRuleController::class, 'index'])->name('working-time-rules.index');
Route::post('/working-time-rules', [WorkingTimeRuleController::class, 'store'])->name('working-time-rules.store');
