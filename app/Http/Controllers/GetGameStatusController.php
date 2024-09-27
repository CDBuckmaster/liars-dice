<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Queries\GameStatusQuery;
use Illuminate\Http\Response;
use Exception;
use App\Models\Game;

final class GetGameStatusController extends Controller
{
  public function __construct(private \Spatie\EventSourcing\Commands\CommandBus $bus) {}

  public function __invoke(Request $request, Game $game)
  {
    try {
      $query = new GameStatusQuery($game->uuid);
      $status = $this->bus->dispatch($query);
    } catch (Exception $exception) {
      return response()->json([
        'error' => $exception->getMessage(),
      ], Response::HTTP_NOT_FOUND);
    }

    return response()->json([
      'status' => $status,
    ], Response::HTTP_OK);
  }
}
