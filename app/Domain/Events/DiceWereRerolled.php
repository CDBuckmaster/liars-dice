<?php

namespace App\Domain\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class DiceWereRerolled extends ShouldBeStored
{
  public function __construct(
    private array $dice
  ) {}

  public function getDice(): array
  {
    return $this->dice;
  }
}
