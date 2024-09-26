<?php

namespace App\Domain\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class GameWasCreated extends ShouldBeStored
{
  public function __construct(
    public array $players,
    public string $firstPlayer,
  ) {}
}
