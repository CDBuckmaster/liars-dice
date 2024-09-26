<?php

use App\Http\Controllers\CreateGameController;
use Illuminate\Support\Facades\Route;

Route::post('games', CreateGameController::class);
