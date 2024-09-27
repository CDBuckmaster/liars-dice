<?php

use App\Http\Controllers\CreateGameController;
use App\Http\Controllers\CreatePlayerActionController;
use App\Http\Controllers\GetNextPlayerController;
use App\Http\Controllers\GetPlayerDiceController;
use Illuminate\Support\Facades\Route;

Route::post('games', CreateGameController::class);
Route::post('games/{game}/actions', CreatePlayerActionController::class);
Route::get('games/{game}/next-player', GetNextPlayerController::class);
Route::get('games/{game}/dice', GetPlayerDiceController::class);
