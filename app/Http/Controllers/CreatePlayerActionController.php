<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePlayerActionRequest;
use App\Domain\Commands\PlayerActionCommandFactory;

final class CreatePlayerActionController extends Controller
{
  public function __construct(private \Spatie\EventSourcing\Commands\CommandBus $bus) {}

  public function __invoke(CreatePlayerActionRequest $request)
  {
    $command = PlayerActionCommandFactory::create(
      $request->get('action'),
      $request->get('game_uuid'),
      $request->get('player'),
      $request->get('arguments')
    );

    $this->bus->dispatch($command);

    return response()->json([
      'uuid' => $command->getGameUuid(),
    ], 201);
  }
}
