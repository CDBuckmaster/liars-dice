<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePlayerActionRequest;
use App\Domain\Commands\PlayerActionCommandFactory;
use App\Domain\Exceptions\GameException;
use Illuminate\Http\Response;
use App\Models\Game;

final class CreatePlayerActionController extends Controller
{
  public function __construct(private \Spatie\EventSourcing\Commands\CommandBus $bus, private PlayerActionCommandFactory $playerActionCommandFactory) {}

  public function __invoke(CreatePlayerActionRequest $request, Game $game)
  {
    try {
      $command = $this->playerActionCommandFactory::create(
        $game->uuid,
        $request->get('action'),
        $request->get('player'),
        $request->get('arguments') ?? []
      );

      $this->bus->dispatch($command);
    } catch (GameException $exception) {
      return response()->json([
        'error' => $exception->getMessage(),
      ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    return response()->json([
      'uuid' => $command->getGameUuid(),
    ], Response::HTTP_CREATED);
  }
}
