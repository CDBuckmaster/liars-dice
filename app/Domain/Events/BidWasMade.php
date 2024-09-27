<?php

namespace App\Domain\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class BidWasMade extends ShouldBeStored
{
  public function __construct(private string $player, private int $quantity, private int $face) {}

  public function getPlayer(): string
  {
    return $this->player;
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
