<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Aggregates\GameAggregate;
use App\Domain\Events\BecamePlayersTurn;
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
      ->when(fn(GameAggregate $game) => $game->createGame(['Han'], 'Han'))
      ->assertRecorded([
        new GameWasCreated(['Han'], 'Han'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
        ])
      ]);
  }

  #[Test]
  public function it_must_let_game_start_first()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->when(fn(GameAggregate $game) => $game->rerollDice(['Han' => [1, 1, 1, 1, 1]]))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_doesnt_let_you_start_game_twice()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given(new GameWasCreated(['Han'], 'Han'))
      ->when(fn(GameAggregate $game) => $game->createGame(['Han'], 'Han'))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_doesnt_let_you_call_before_bid()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given(new GameWasCreated(['Han'], 'Han'))
      ->when(fn(GameAggregate $game) => $game->playerCalledBluff('Han'))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_lets_you_make_a_bid()
  {
    GameAggregate::fake()
      ->given([
        new GameWasCreated(['Han', 'Luke'], 'Han'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
        ])
      ])
      ->when(fn(GameAggregate $game) => $game->playerMadeBid('Han', 1, 1))
      ->assertRecorded([
        new BidWasMade('Han', 1, 1),
        new BecamePlayersTurn('Luke')
      ]);
  }

  #[Test]
  public function it_only_allows_current_player_to_bid()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given([
        new GameWasCreated(['Han', 'Luke'], 'Han'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
        ])
      ])
      ->when(fn(GameAggregate $game) => $game->playerMadeBid('Luke', 1, 1))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_only_allows_bids_with_at_least_one_dice()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given([
        new GameWasCreated(['Han', 'Luke'], 'Han'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
        ])
      ])
      ->when(fn(GameAggregate $game) => $game->playerMadeBid('Han', 0, 1))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_only_allows_bids_under_the_number_of_dice()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given([
        new GameWasCreated(['Han', 'Luke'], 'Han'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
        ])
      ])
      ->when(fn(GameAggregate $game) => $game->playerMadeBid('Han', 11, 1))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_doesnt_allow_bids_with_lower_quantity()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given([
        new GameWasCreated(['Han', 'Luke'], 'Han'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('Han', 2, 1)
      ])
      ->when(fn(GameAggregate $game) => $game->playerMadeBid('Luke', 1, 1))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_doesnt_allow_identical_bids()
  {
    $this->expectException(GameException::class);

    GameAggregate::fake()
      ->given([
        new GameWasCreated(['Han', 'Luke'], 'Han'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('Han', 2, 1)
      ])
      ->when(fn(GameAggregate $game) => $game->playerMadeBid('Luke', 2, 1))
      ->assertNothingRecorded();
  }

  #[Test]
  public function it_handles_correct_bluff_calls()
  {
    GameAggregate::fake()
      ->given([
        new GameWasCreated(['Han', 'Luke'], 'Han'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('Han', 1, 2)
      ])
      ->when(fn(GameAggregate $game) => $game->playerCalledBluff('Luke'))
      ->assertRecorded([
        new BluffWasCalled('Luke'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
        ]),
        new BecamePlayersTurn('Han')
      ]);
  }

  #[Test]
  public function it_handles_incorrect_bluff_calls()
  {
    GameAggregate::fake()
      ->given([
        new GameWasCreated(['Han', 'Luke'], 'Han'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('Han', 1, 1)
      ])
      ->when(fn(GameAggregate $game) => $game->playerCalledBluff('Luke'))
      ->assertRecorded([
        new BluffWasCalled('Luke'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1],
        ]),
        new BecamePlayersTurn('Luke')
      ]);
  }

  #[Test]
  public function it_handles_correct_spot_on_calls()
  {
    GameAggregate::fake()
      ->given([
        new GameWasCreated(['Han', 'Luke', 'Leia'], 'Han'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
          'Leia' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('Han', 15, 1)
      ])
      ->when(fn(GameAggregate $game) => $game->playerCalledSpotOn('Luke'))
      ->assertRecorded([
        new SpotOnWasCalled('Luke'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
          'Leia' => [1, 1, 1, 1],
        ]),
        new BecamePlayersTurn('Leia')
      ]);
  }

  #[Test]
  public function it_handles_incorrect_spot_on_calls()
  {
    GameAggregate::fake()
      ->given([
        new GameWasCreated(['Han', 'Luke', 'Leia'], 'Han'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
          'Leia' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('Han', 14, 1)
      ])
      ->when(fn(GameAggregate $game) => $game->playerCalledSpotOn('Luke'))
      ->assertRecorded([
        new SpotOnWasCalled('Luke'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1],
          'Leia' => [1, 1, 1, 1, 1],
        ]),
        new BecamePlayersTurn('Luke')
      ]);
  }

  #[Test]
  public function it_handles_game_over()
  {
    GameAggregate::fake()
      ->given([
        new GameWasCreated(['Han', 'Luke'], 'Han'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('Han', 1, 2),
        new BluffWasCalled('Luke'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('Han', 1, 2),
        new BluffWasCalled('Luke'),
        new DiceWereRerolled([
          'Han' => [1, 1, 1],
          'Luke' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('Han', 1, 2),
        new BluffWasCalled('Luke'),
        new DiceWereRerolled([
          'Han' => [1, 1],
          'Luke' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('Han', 1, 2),
        new BluffWasCalled('Luke'),
        new DiceWereRerolled([
          'Han' => [1],
          'Luke' => [1, 1, 1, 1, 1],
        ]),
        new BidWasMade('Han', 1, 2),
      ])
      ->when(fn(GameAggregate $game) => $game->playerCalledBluff('Luke'))
      ->assertRecorded([
        new BluffWasCalled('Luke'),
        new GameEnded('Luke'),
      ]);
  }
}

namespace App\Domain\Aggregates;

function random_int($min, $max)
{
  return 1;
}
