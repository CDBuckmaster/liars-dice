<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Aggregates\GameAggregate;
use App\Domain\Events\DiceWereRerolled;
use App\Domain\Events\GameWasCreated;
use App\Domain\Events\BidWasMade;
use App\Domain\Events\BluffWasCalled;
use App\Domain\Events\GameEnded;
use App\Domain\Events\SpotOnWasCalled;
use PHPUnit\Framework\Attributes\Test;
use App\Domain\Exceptions\GameException;

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

  #[Test]
  public function it_must_let_game_start_first()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->when(fn(GameAggregate $game) => $game->rerollDice(['player-1' => [1, 1, 1, 1, 1]]))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_doesnt_let_you_start_game_twice()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given(new GameWasCreated(['player-1'], 'player-1'))
      ->when(fn(GameAggregate $game) => $game->createGame(['player-1'], 'player-1'))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_doesnt_let_you_call_before_bid()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given(new GameWasCreated(['player-1'], 'player-1'))
      ->when(fn(GameAggregate $game) => $game->playerCalledBluff('player-1'))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_lets_you_make_a_bid()
  {
    GameAggregate::fake()
      ->given([
        new GameWasCreated(['player-1', 'player-2'], 'player-1'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
        ])
      ])
      ->when(fn(GameAggregate $game) => $game->playerMadeBid('player-1', 1, 1))
      ->assertRecorded(new BidWasMade('player-1', 1, 1));
  }

  #[Test]
  public function it_only_allows_current_player_to_bid()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given([
        new GameWasCreated(['player-1', 'player-2'], 'player-1'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
        ])
      ])
      ->when(fn(GameAggregate $game) => $game->playerMadeBid('player-2', 1, 1))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_only_allows_bids_with_at_least_one_dice()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given([
        new GameWasCreated(['player-1', 'player-2'], 'player-1'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
        ])
      ])
      ->when(fn(GameAggregate $game) => $game->playerMadeBid('player-1', 0, 1))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_only_allows_bids_under_the_number_of_dice()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given([
        new GameWasCreated(['player-1', 'player-2'], 'player-1'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
        ])
      ])
      ->when(fn(GameAggregate $game) => $game->playerMadeBid('player-1', 11, 1))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_doesnt_allow_bids_with_lower_quantity()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given([
        new GameWasCreated(['player-1', 'player-2'], 'player-1'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('player-1', 2, 1)
      ])
      ->when(fn(GameAggregate $game) => $game->playerMadeBid('player-2', 1, 1))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_doesnt_allow_identical_bids()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given([
        new GameWasCreated(['player-1', 'player-2'], 'player-1'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('player-1', 2, 1)
      ])
      ->when(fn(GameAggregate $game) => $game->playerMadeBid('player-2', 2, 1))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_handles_correct_bluff_calls()
  {
    GameAggregate::fake()
      ->given([
        new GameWasCreated(['player-1', 'player-2'], 'player-1'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('player-1', 1, 2)
      ])
      ->when(fn(GameAggregate $game) => $game->playerCalledBluff('player-2'))
      ->assertRecorded([
        new BluffWasCalled('player-2'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
        ])
      ]);
  }

  #[Test]
  public function it_handles_incorrect_bluff_calls()
  {
    GameAggregate::fake()
      ->given([
        new GameWasCreated(['player-1', 'player-2'], 'player-1'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('player-1', 1, 1)
      ])
      ->when(fn(GameAggregate $game) => $game->playerCalledBluff('player-2'))
      ->assertRecorded([
        new BluffWasCalled('player-2'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1],
        ])
      ]);
  }

  #[Test]
  public function it_handles_correct_spot_on_calls()
  {
    GameAggregate::fake()
      ->given([
        new GameWasCreated(['player-1', 'player-2', 'player-3'], 'player-1'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
          'player-3' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('player-1', 15, 1)
      ])
      ->when(fn(GameAggregate $game) => $game->playerCalledSpotOn('player-2'))
      ->assertRecorded([
        new SpotOnWasCalled('player-2'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
          'player-3' => [1, 1, 1, 1],
        ])
      ]);
  }

  #[Test]
  public function it_handles_incorrect_spot_on_calls()
  {
    GameAggregate::fake()
      ->given([
        new GameWasCreated(['player-1', 'player-2', 'player-3'], 'player-1'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
          'player-3' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('player-1', 14, 1)
      ])
      ->when(fn(GameAggregate $game) => $game->playerCalledSpotOn('player-2'))
      ->assertRecorded([
        new SpotOnWasCalled('player-2'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1],
          'player-3' => [1, 1, 1, 1, 1],
        ])
      ]);
  }

  #[Test]
  public function it_handles_game_over()
  {
    GameAggregate::fake()
      ->given([
        new GameWasCreated(['player-1', 'player-2'], 'player-1'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('player-1', 1, 2),
        new BluffWasCalled('player-2'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('player-1', 1, 2),
        new BluffWasCalled('player-2'),
        new DiceWereRerolled([
          'player-1' => [1, 1, 1],
          'player-2' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('player-1', 1, 2),
        new BluffWasCalled('player-2'),
        new DiceWereRerolled([
          'player-1' => [1, 1],
          'player-2' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('player-1', 1, 2),
        new BluffWasCalled('player-2'),
        new DiceWereRerolled([
          'player-1' => [1],
          'player-2' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('player-1', 1, 2),
      ])
      ->when(fn(GameAggregate $game) => $game->playerCalledBluff('player-2'))
      ->assertRecorded([
        new BluffWasCalled('player-2'),
        new GameEnded('player-2'),
      ]);
  }
}

namespace App\Domain\Aggregates;

function random_int($min, $max)
{
  return 1;
}
