<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Hello, Importador!',
    ]);
});

Route::post('/upload', [UserController::class, 'upload']);
Route::get('/users', [UserController::class, 'index']);
