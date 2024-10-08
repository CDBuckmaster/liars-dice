<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Queries\PlayerDiceQuery;
use Illuminate\Http\Response;
use Exception;
use App\Models\Game;

final class GetPlayerDiceController extends Controller
{
  public function __construct(private \Spatie\EventSourcing\Commands\CommandBus $bus) {}

  public function __invoke(Request $request, Game $game, string $player)
  {
    try {
      $query = new PlayerDiceQuery($game->uuid, $player);
      $dice = $this->bus->dispatch($query);
    } catch (Exception $exception) {
      return response()->json([
        'error' => $exception->getMessage(),
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'dice' => $dice,
    ], Response::HTTP_OK);
  }
}
