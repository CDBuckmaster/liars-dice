<?php

namespace App\Domain\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class GameEnded extends ShouldBeStored
{
  public function __construct(private string $winner) {}

  public function getWinner(): string
  {
    return $this->winner;
  }
}
