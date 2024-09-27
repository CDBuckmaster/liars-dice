<?php

namespace App\Domain\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class SpotOnWasCalled extends ShouldBeStored
{
  public function __construct(
    private string $player,
  ) {}

  public function getPlayer(): string
  {
    return $this->player;
  }
}
