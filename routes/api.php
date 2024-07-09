<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Bitrix24Controller;
use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/auth/bitrix24/callback', [Bitrix24Controller::class, 'handleProviderCallback']);
Route::get('/auth/bitrix24', [Bitrix24Controller::class, 'redirectToProvider']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('companies', CompanyController::class);
});
