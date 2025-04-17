<?php

use App\Http\Controllers\ImportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Hello, Importador!',
    ]);
});

Route::post('/upload', [ImportController::class, 'upload']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/import-status/{id}', [ImportController::class, 'getStatus']);