<?php

namespace App\Domain\CommandHandlers;

use App\Domain\Aggregates\GameAggregate;
use App\Domain\Commands\CreateGameCommand;
use App\Models\Game;

class CreateGameCommandHandler
{
  // @todo: Move this elsewhere, might need to define a game entity for this
  const STARTING_DICE = 5;

  public function __invoke(CreateGameCommand $command)
  {
    $startingPlayer = $command->players[array_rand($command->players)];
    $game = new Game();
    $game->uuid = $command->gameUuid;
    $game->metadata = [
      'players' => $command->players,
      'starting_player' => $startingPlayer,
    ];
    $game->save();

    $startingDice = rollDicePerPlayer(array_fill_keys($command->players, self::STARTING_DICE));


    GameAggregate::retrieve($command->gameUuid)
      ->createGame($command->players, $startingPlayer)
      ->rerollDice($startingDice)
      ->persist();
  }
}
