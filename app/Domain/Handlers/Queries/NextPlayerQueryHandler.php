<?php

namespace App\Domain\Handlers\Queries;

use App\Domain\Events\BecamePlayersTurn;
use App\Domain\Queries\NextPlayerQuery;
use App\Models\Game;
use Exception;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

final class NextPlayerQueryHandler
{
  public function __construct(private EloquentStoredEvent $storedEvent) {}

  public function __invoke(NextPlayerQuery $query)
  {
    $game = Game::where('uuid', $query->getGameUuid())->first();
    if (!$game) {
      throw new Exception('Game not found');
    }

    $result = $this->storedEvent->query()
      ->where('aggregate_uuid', $query->getGameUuid())
      ->whereEvent(BecamePlayersTurn::class)
      ->latest()
      ->first();

    if ($result === null) {
      return $game->metadata->starting_player;
    } else {
      return $result->toStoredEvent()->event->getPlayer();
    }
  }
}
