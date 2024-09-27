<?php

namespace App\Domain\CommandHandlers;

use App\Domain\Aggregates\GameAggregate;
use App\Domain\Commands\MakeBidCommand;

class MakeBidCommandHandler
{
  public function __construct(private GameAggregate $gameAggregate) {}

  public function __invoke(MakeBidCommand $command)
  {
    $this->gameAggregate::retrieve($command->getGameUuid())
      ->playerMadeBid($command->getPlayerName(), $command->getQuantity(), $command->getFace())
      ->persist();
  }
}
