<?php

namespace App\Domain\Commands;

use Spatie\EventSourcing\Commands\HandledBy;
use App\Domain\CommandHandlers\CallBluffCommandHandler;

#[HandledBy(CallBluffCommandHandler::class)]
final class CallBluffCommand extends PlayerActionCommand implements CommandInterface
{

  /**
   * CallBluffCommand constructor.
   *
   * @param string $gameUuid
   * @param string $playerName
   */
  public function __construct(string $gameUuid, string $playerName)
  {
    parent::__construct($gameUuid, $playerName);
  }
}
