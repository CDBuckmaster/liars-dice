<?php

namespace App\Domain\CommandHandlers;

use App\Domain\Aggregates\GameAggregate;
use App\Domain\Commands\CreateGameCommand;
use App\Models\Game;

class CreateGameCommandHandler
{
  // @todo: Move this elsewhere, might need to define a game entity for this
  const STARTING_DICE = 5;

  public function __construct(private GameAggregate $gameAggregate) {}

  public function __invoke(CreateGameCommand $command)
  {
    $startingPlayer = $command->getPlayers()[array_rand($command->getPlayers())];
    $game = new Game();
    $game->uuid = $command->getGameUuid();
    $game->metadata = [
      'players' => $command->getPlayers(),
      'starting_player' => $startingPlayer,
    ];
    $game->save();

    $startingDice = rollDicePerPlayer(array_fill_keys($command->getPlayers(), self::STARTING_DICE));

    $this->gameAggregate::retrieve($command->getGameUuid())
      ->createGame($command->getPlayers(), $startingPlayer)
      ->rerollDice($startingDice)
      ->persist();
  }
}
