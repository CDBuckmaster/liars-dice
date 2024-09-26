<?php

use App\Http\Controllers\CreateGameController;
use App\Http\Controllers\CreatePlayerActionController;
use Illuminate\Support\Facades\Route;

Route::post('games', CreateGameController::class);
Route::post('games/actions', CreatePlayerActionController::class);
