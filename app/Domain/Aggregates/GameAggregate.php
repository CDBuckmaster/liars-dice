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
  private ?string $lastPlayer = null;
  private ?int $lastBidQuantity = null;
  private ?int $lastBidFace = null;
  private ?array $diceValues = null;
  private ?array $diceCount = null;
  private bool $gameOver = false;

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

  public function applyGameWasCreated(GameWasCreated $event): void
  {
    if ($this->nextPlayer !== null) {
      throw new \Exception('Game already created');
    }
    $this->nextPlayer = $event->firstPlayer;
    $this->diceValues = array_fill(0, count($event->players), 0);
    $this->diceCount = array_fill(0, count($event->players), 5);
  }

  public function applyDiceWereRerolled(DiceWereRerolled $event): void
  {
    if ($this->gameOver) {
      throw new \Exception('Game is over');
    }
    if ($this->diceValues === null) {
      throw new \Exception('Game not created');
    }
    if ($this->lastBidQuantity !== null) {
      throw new \Exception('Cannot reroll dice before bid');
    }
    $this->diceValues = $event->dice;
  }

  public function applyBidWasMade(BidWasMade $event): void
  {
    if ($this->gameOver) {
      throw new \Exception('Game is over');
    }
    if ($this->diceValues === null) {
      throw new \Exception('Game not created');
    }
    if ($event->quantity < 1) {
      throw new \Exception('Bid quantity must be at least 1');
    }
    if ($event->face < 1 || $event->face > 6) {
      throw new \Exception('Bid face must be between 1 and 6');
    }
    if ($event->quantity > array_sum($this->diceCount)) {
      throw new \Exception('Bid quantity cannot be higher than total dice count');
    }
    if ($this->lastBidQuantity !== null && $this->lastBidFace !== null) {
      if ($event->quantity < $this->lastBidQuantity) {
        throw new \Exception('Bid quantity cannot be lower than last bid');
      }
      if ($event->quantity === $this->lastBidQuantity && $event->face <= $this->lastBidFace) {
        throw new \Exception('Bid face cannot be lower than or equal to last bid');
      }
    }

    $this->lastBidQuantity = $event->quantity;
    $this->lastBidFace = $event->face;
    $this->goToNextPlayer();
  }

  public function applyBluffWasCalled(BluffWasCalled $event): void
  {
    if ($this->gameOver) {
      throw new \Exception('Game is over');
    }
    if ($this->diceValues === null) {
      throw new \Exception('Game not created');
    }
    if ($this->lastBidQuantity === null || $this->lastBidFace === null) {
      throw new \Exception('Cannot call bluff before bid');
    }

    $diceCount = 0;
    foreach ($this->diceValues as $player => $values) {
      $diceCount += count(array_filter($values, fn($value) => $value === $this->lastBidFace));
    }

    if ($diceCount < $this->lastBidQuantity) {
      $this->playerLosesDice($this->lastPlayer);
    } else {
      $this->playerLosesDice($event->player);
    }
  }

  public function applySpotOnWasCalled(SpotOnWasCalled $event): void
  {
    if ($this->gameOver) {
      throw new \Exception('Game is over');
    }
    if ($this->diceValues === null) {
      throw new \Exception('Game not created');
    }
    if ($this->lastBidQuantity === null || $this->lastBidFace === null) {
      throw new \Exception('Cannot call bluff before bid');
    }

    $diceCount = 0;
    foreach ($this->diceValues as $player => $values) {
      $diceCount += count(array_filter($values, fn($value) => $value === $this->lastBidFace));
    }

    if ($diceCount == $this->lastBidQuantity) {
      $this->allOtherPlayersLoseDice($this->lastPlayer);
    } else {
      $this->playerLosesDice($event->player);
    }
  }

  private function playerLosesDice($player): void
  {
    $this->diceCount[$player] -= 1;
    if ($this->isGameOver()) {
      $this->gameOver = true;
    } else {
      if ($this->diceCount[$player] > 0) {
        $this->nextPlayer = $player;
      } else {
        $this->goToNextPlayer();
      }
    }
    $this->lastPlayer = null;
    $this->lastBidQuantity = null;
    $this->lastBidFace = null;
  }

  private function allOtherPlayersLoseDice($player): void
  {
    foreach ($this->diceCount as $player => $count) {
      if ($player !== $this->lastPlayer && $count > 0) {
        $this->diceCount[$player] -= 1;
      }
    }
    if ($this->isGameOver()) {
      $this->gameOver = true;
    } else {
      $this->goToNextPlayer();
    }
    $this->lastPlayer = null;
    $this->lastBidQuantity = null;
    $this->lastBidFace = null;
  }

  private function goToNextPlayer(): void
  {
    $this->lastPlayer = $this->nextPlayer;

    $remainingPlayers = array_keys(array_filter($this->diceCount, function ($count, $player) {
      return $player !== $this->nextPlayer && $count > 0;
    }));
    $index = array_search($this->nextPlayer, $remainingPlayers);
    if ($index < count($remainingPlayers) - 1) {
      $this->nextPlayer = $remainingPlayers[$index + 1];
    } else {
      $this->nextPlayer = $remainingPlayers[0];
    }
  }

  private function isGameOver(): bool
  {
    $remainingPlayers = array_filter($this->diceCount, fn($count) => $count > 0);
    if (count($remainingPlayers) > 1) {
      return false;
    }
    return true;
  }
}
