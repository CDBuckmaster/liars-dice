<?php

namespace App\Domain\Queries;

use Spatie\EventSourcing\Commands\HandledBy;
use App\Domain\Handlers\Queries\GameStatusQueryHandler;

#[HandledBy(GameStatusQueryHandler::class)]
final class GameStatusQuery implements QueryInterface
{

  /**
   * GameStatusQuery constructor.
   *
   * @param string $gameUuid
   */
  public function __construct(private string $gameUuid) {}

  public function getGameUuid(): string
  {
    return $this->gameUuid;
  }
}
