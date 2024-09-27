<?php

namespace App\Domain\Queries;

use Spatie\EventSourcing\Commands\HandledBy;
use App\Domain\Handlers\Queries\PlayerDiceQueryHandler;

#[HandledBy(PlayerDiceQueryHandler::class)]
final class PlayerDiceQuery implements QueryInterface
{

  /**
   * PlayerDiceQuery constructor.
   *
   * @param string $gameUuid
   * @param string $player
   */
  public function __construct(private string $gameUuid, private string $player) {}

  public function getGameUuid(): string
  {
    return $this->gameUuid;
  }

  public function getPlayer(): string
  {
    return $this->player;
  }
}
