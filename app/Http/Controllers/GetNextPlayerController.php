<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Queries\NextPlayerQuery;
use Illuminate\Http\Response;
use Exception;
use App\Models\Game;

final class GetNextPlayerController extends Controller
{
  public function __construct(private \Spatie\EventSourcing\Commands\CommandBus $bus) {}

  public function __invoke(Request $request, Game $game)
  {
    try {
      $query = new NextPlayerQuery($game->uuid);
      $player = $this->bus->dispatch($query);
    } catch (Exception $exception) {
      return response()->json([
        'error' => $exception->getMessage(),
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'player' => $player,
    ], Response::HTTP_OK);
  }
}
