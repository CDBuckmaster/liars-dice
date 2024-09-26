<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGameRequest;
use App\Domain\Commands\CreateGameCommand;

final class CreateGameController extends Controller
{
    public function __construct(private \Spatie\EventSourcing\Commands\CommandBus $bus) {}

    public function __invoke(CreateGameRequest $request)
    {
        $command = new CreateGameCommand($request->get('players'));
        $this->bus->dispatch($command);

        return response()->json([
            'uuid' => $command->gameUuid,
        ], 201);
    }
}
