<?php

namespace App\Domain\CommandHandlers;

use App\Domain\Aggregates\GameAggregate;
use App\Domain\Commands\CallSpotOnCommand;

class CallSpotOnCommandHandler
{
  public function __construct(private GameAggregate $gameAggregate) {}

  public function __invoke(CallSpotOnCommand $command)
  {
    $this->gameAggregate::retrieve($command->getGameUuid())
      ->playerCalledSpotOn($command->getPlayerName())
      ->persist();
  }
}
