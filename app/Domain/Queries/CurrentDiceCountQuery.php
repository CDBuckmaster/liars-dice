<?php

namespace App\Domain\Queries;

use Spatie\EventSourcing\Commands\HandledBy;
use App\Domain\Handlers\Queries\CurrentDiceCountQueryHandler;

#[HandledBy(CurrentDiceCountQueryHandler::class)]
final class CurrentDiceCountQuery implements QueryInterface
{

  /**
   * CurrentDiceCountQuery constructor.
   *
   * @param string $gameUuid
   */
  public function __construct(private string $gameUuid) {}

  public function getGameUuid(): string
  {
    return $this->gameUuid;
  }
}
