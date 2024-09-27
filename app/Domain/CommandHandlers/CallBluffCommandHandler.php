<?php

namespace App\Domain\CommandHandlers;

use App\Domain\Aggregates\GameAggregate;
use App\Domain\Commands\CallBluffCommand;

class CallBluffCommandHandler
{
  public function __construct(private GameAggregate $gameAggregate) {}

  public function __invoke(CallBluffCommand $command)
  {
    $this->gameAggregate::retrieve($command->getGameUuid())
      ->playerCalledBluff($command->getPlayerName())
      ->persist();
  }
}
