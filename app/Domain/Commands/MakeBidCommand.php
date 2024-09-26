<?php

namespace App\Domain\Commands;

use Spatie\EventSourcing\Commands\HandledBy;
use App\Domain\CommandHandlers\MakeBidCommandHandler;

#[HandledBy(MakeBidCommandHandler::class)]
final class MakeBidCommand extends PlayerActionCommand implements CommandInterface
{
  protected int $quantity;
  protected int $face;

  /**
   * MakeBidCommand constructor.
   *
   * @param string $gameUuid
   * @param string $playerName
   * @param int $quantity
   * @param int $face
   */
  public function __construct(string $gameUuid, string $playerName, int $quantity, int $face)
  {
    parent::__construct($gameUuid, $playerName);

    $this->quantity = $quantity;
    $this->face = $face;
  }

  public function getQuantity(): int
  {
    return $this->quantity;
  }

  public function getFace(): int
  {
    return $this->face;
  }
}
