<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGameRequest;
use App\Domain\Commands\CreateGameCommand;
use App\Domain\Exceptions\GameException;
use Illuminate\Http\Response;

final class CreateGameController extends Controller
{
  public function __construct(private \Spatie\EventSourcing\Commands\CommandBus $bus) {}

  public function __invoke(CreateGameRequest $request)
  {
    try {
      $command = new CreateGameCommand($request->get('players'));
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
