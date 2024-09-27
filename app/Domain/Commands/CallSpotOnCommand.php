<?php

namespace App\Domain\Commands;

use Spatie\EventSourcing\Commands\HandledBy;
use App\Domain\Handlers\Commands\CallSpotOnCommandHandler;

#[HandledBy(CallSpotOnCommandHandler::class)]
final class CallSpotOnCommand extends PlayerActionCommand implements CommandInterface
{

  /**
   * CallSpotOnCommand constructor.
   *
   * @param string $gameUuid
   * @param string $playerName
   */
  public function __construct(string $gameUuid, string $playerName)
  {
    parent::__construct($gameUuid, $playerName);
  }
}
