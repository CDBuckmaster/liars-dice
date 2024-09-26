<?php

namespace App\Domain\CommandHandlers;

use App\Domain\Aggregates\GameAggregate;
use App\Domain\Commands\CallBluffCommand;

class CallBluffCommandHandler
{
  public function __invoke(CallBluffCommand $command)
  {
    GameAggregate::retrieve($command->getGameUuid())
      ->playerCalledBluff($command->getPlayerName())
      ->persist();
  }
}
