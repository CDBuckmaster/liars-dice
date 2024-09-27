<?php

namespace App\Domain\Handlers\Queries;

use App\Domain\Events\DiceWereRerolled;
use App\Domain\Queries\CurrentDiceCountQuery;
use App\Models\Game;
use Exception;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

final class CurrentDiceCountQueryHandler
{
  public function __construct(private EloquentStoredEvent $storedEvent) {}

  public function __invoke(CurrentDiceCountQuery $query)
  {
    $game = Game::where('uuid', $query->getGameUuid())->first();
    if (!$game) {
      throw new Exception('Game not found');
    }

    $result = $this->storedEvent->query()
      ->where('aggregate_uuid', $query->getGameUuid())
      ->whereEvent(DiceWereRerolled::class)
      ->latest()
      ->first();

    if ($result === null) {
      throw new Exception('No dice were rolled');
    }

    $dice = $result->toStoredEvent()->event->getDice();
    return array_map(fn($player) => count($player), $dice);
  }
}
