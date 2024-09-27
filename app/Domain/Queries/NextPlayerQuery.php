<?php

namespace App\Domain\Queries;

use Spatie\EventSourcing\Commands\HandledBy;
use App\Domain\Handlers\Queries\NextPlayerQueryHandler;

#[HandledBy(NextPlayerQueryHandler::class)]
final class NextPlayerQuery implements QueryInterface
{

  /**
   * NextPlayerQuery constructor.
   *
   * @param string $gameUuid
   */
  public function __construct(private string $gameUuid) {}

  public function getGameUuid(): string
  {
    return $this->gameUuid;
  }
}
