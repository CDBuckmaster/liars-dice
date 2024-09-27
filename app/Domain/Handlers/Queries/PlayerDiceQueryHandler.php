<?php

namespace App\Domain\Handlers\Queries;

use App\Domain\Events\DiceWereRerolled;
use App\Domain\Queries\PlayerDiceQuery;
use App\Models\Game;
use Exception;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

final class PlayerDiceQueryHandler
{
  public function __construct(private EloquentStoredEvent $storedEvent) {}

  public function __invoke(PlayerDiceQuery $query)
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
    if (!isset($dice[$query->getPlayer()])) {
      throw new Exception('Player not found');
    }

    return $dice[$query->getPlayer()];
  }
}
