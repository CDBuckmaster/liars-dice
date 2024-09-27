<?php

namespace App\Domain\Handlers\Queries;

use App\Domain\Events\BecamePlayersTurn;
use App\Domain\Queries\GameStatusQuery;
use App\Models\Game;
use Exception;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

final class GameStatusQueryHandler
{
  public function __construct(private EloquentStoredEvent $storedEvent) {}

  public function __invoke(GameStatusQuery $query)
  {
    $game = Game::where('uuid', $query->getGameUuid())->first();
    if (!$game) {
      throw new Exception('Game not found');
    }

    if ($game->completed_at) {
      return 'completed';
    } else {
      return 'in progress';
    }
  }
}
