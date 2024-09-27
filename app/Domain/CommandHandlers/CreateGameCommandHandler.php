<?php

namespace App\Domain\CommandHandlers;

use App\Domain\Aggregates\GameAggregate;
use App\Domain\Commands\CreateGameCommand;
use App\Models\Game;

final class CreateGameCommandHandler
{
  public function __construct(private GameAggregate $gameAggregate) {}

  public function __invoke(CreateGameCommand $command)
  {
    // Create initial state
    $startingPlayer = $command->getPlayers()[array_rand($command->getPlayers())];
    $game = new Game();
    $game->uuid = $command->getGameUuid();
    $game->metadata = [
      'players' => $command->getPlayers(),
      'starting_player' => $startingPlayer,
    ];
    $game->save();

    // Begin event sourcing
    $this->gameAggregate::retrieve($command->getGameUuid())
      ->createGame($command->getPlayers(), $startingPlayer)
      ->persist();
  }
}
