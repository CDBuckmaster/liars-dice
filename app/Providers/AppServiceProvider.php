<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Game;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Route::bind('game', function (string $value) {
      return Game::where('uuid', $value)->firstOrFail();
    });
  }
}
