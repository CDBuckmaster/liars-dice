<?php

namespace App\Domain\Handlers\Commands;

use App\Domain\Aggregates\GameAggregate;
use App\Domain\Commands\CallSpotOnCommand;

final class CallSpotOnCommandHandler
{
  public function __construct(private GameAggregate $gameAggregate) {}

  public function __invoke(CallSpotOnCommand $command)
  {
    $this->gameAggregate::retrieve($command->getGameUuid())
      ->playerCalledSpotOn($command->getPlayerName())
      ->persist();
  }
}
