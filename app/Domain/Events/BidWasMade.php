<?php

namespace App\Domain\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class BidWasMade extends ShouldBeStored
{
  public function __construct(
    public string $player,
    public int $quantity,
    public int $face,
  ) {}
}
