<?php

namespace App\Domain\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class GameWasCreated extends ShouldBeStored
{
  public function __construct(
    private array $players,
    private string $firstPlayer,
  ) {}

  public function getPlayers(): array
  {
    return $this->players;
  }

  public function getFirstPlayer(): string
  {
    return $this->firstPlayer;
  }
}
