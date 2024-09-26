<?php

namespace App\Domain\Aggregates;

use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use App\Domain\Events\GameWasCreated;
use App\Domain\Events\DiceWereRerolled;
use App\Domain\Events\BidWasMade;
use App\Domain\Events\BluffWasCalled;
use App\Domain\Events\SpotOnWasCalled;

final class GameAggregate extends AggregateRoot
{
  private ?string $nextPlayer = null;
  private ?array $lastBid = null;
  private ?array $diceValues = null;
  private ?array $diceCount = null;

  public function createGame(array $players, string $firstPlayer): self
  {
    $this->recordThat(new GameWasCreated($players, $firstPlayer));

    return $this;
  }

  public function rerollDice(array $dice): self
  {
    $this->recordThat(new DiceWereRerolled($dice));

    return $this;
  }

  public function playerMadeBid(string $player, int $quantity, int $face): self
  {
    $this->recordThat(new BidWasMade($player, $quantity, $face));

    return $this;
  }

  public function playerCalledBluff(string $player): self
  {
    $this->recordThat(new BluffWasCalled($player));

    return $this;
  }

  public function playerCalledSpotOn(string $player): self
  {
    $this->recordThat(new SpotOnWasCalled($player));

    return $this;
  }
}
