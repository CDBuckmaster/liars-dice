<?php

namespace App\Domain\CommandHandlers;

use App\Domain\Aggregates\GameAggregate;
use App\Domain\Commands\CallSpotOnCommand;

class CallSpotOnCommandHandler
{
  public function __invoke(CallSpotOnCommand $command)
  {
    GameAggregate::retrieve($command->getGameUuid())
      ->playerCalledSpotOn($command->getPlayerName())
      ->persist();
  }
}
