<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Aggregates\GameAggregate;
use App\Domain\Events\DiceWereRerolled;
use App\Domain\Events\GameWasCreated;
use PHPUnit\Framework\Attributes\Test;

class GameAggregateTest extends TestCase
{

  #[Test]
  public function it_can_start_game()
  {

    GameAggregate::fake()
      ->when(fn(GameAggregate $game) => $game->createGame(['player-1'], 'player-1'))
      ->assertRecorded([
        new GameWasCreated(['player-1'], 'player-1'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
        ])
      ]);
  }
}

namespace App\Domain\Aggregates;

function random_int($min, $max)
{
  return 1;
}
