<?php

namespace App\Domain\Handlers\Commands;

use App\Domain\Aggregates\GameAggregate;
use App\Domain\Commands\CallBluffCommand;

final class CallBluffCommandHandler
{
  public function __construct(private GameAggregate $gameAggregate) {}

  public function __invoke(CallBluffCommand $command)
  {
    $this->gameAggregate::retrieve($command->getGameUuid())
      ->playerCalledBluff($command->getPlayerName())
      ->persist();
  }
}
