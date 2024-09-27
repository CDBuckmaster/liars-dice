<?php

namespace App\Domain\Aggregates;

use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use App\Domain\Events\GameWasCreated;
use App\Domain\Events\DiceWereRerolled;
use App\Domain\Events\BidWasMade;
use App\Domain\Events\BluffWasCalled;
use App\Domain\Events\SpotOnWasCalled;
use App\Domain\Events\GameEnded;
use App\Domain\Exceptions\GameException;
use App\Models\Game;

final class GameAggregate extends AggregateRoot
{
  private ?string $nextPlayer = null;
  private ?string $lastPlayer = null;
  private ?int $lastBidQuantity = null;
  private ?int $lastBidFace = null;
  private ?array $diceValues = null;
  private ?array $diceCount = null;
  private bool $gameOver = false;
  private ?string $winner = null;

  public function createGame(array $players, string $firstPlayer): self
  {
    if ($this->nextPlayer !== null) {
      throw new GameException('Game already created');
    }

    $this->recordThat(new GameWasCreated($players, $firstPlayer));
    $this->rerollDiceAndRecord();

    return $this;
  }

  public function rerollDice(array $dice): self
  {
    if ($this->gameOver) {
      throw new GameException('Game is over');
    }
    if ($this->diceValues === null) {
      throw new GameException('Game not created');
    }

    $this->recordThat(new DiceWereRerolled($dice));

    return $this;
  }

  public function playerMadeBid(string $player, int $quantity, int $face): self
  {
    if ($this->gameOver) {
      throw new GameException('Game is over');
    }
    if ($this->nextPlayer !== $player) {
      throw new GameException('Not player\'s turn');
    }
    if ($this->diceValues === null) {
      throw new GameException('Game not created');
    }
    if ($quantity < 1) {
      throw new GameException('Bid quantity must be at least 1');
    }
    if ($face < 1 || $face > 6) {
      throw new GameException('Bid face must be between 1 and 6');
    }
    if ($quantity > array_sum($this->diceCount)) {
      throw new GameException('Bid quantity cannot be higher than total dice count');
    }
    if ($this->lastBidQuantity !== null && $this->lastBidFace !== null) {
      if ($quantity < $this->lastBidQuantity) {
        throw new GameException('Bid quantity cannot be lower than last bid');
      }
      if ($quantity === $this->lastBidQuantity && $face <= $this->lastBidFace) {
        throw new GameException('Bid face cannot be lower than or equal to last bid');
      }
    }

    $this->recordThat(new BidWasMade($player, $quantity, $face));

    return $this;
  }

  public function playerCalledBluff(string $player): self
  {
    if ($this->gameOver) {
      throw new GameException('Game is over');
    }
    if ($this->diceValues === null) {
      throw new GameException('Game not created');
    }
    if ($this->lastBidQuantity === null || $this->lastBidFace === null) {
      throw new GameException('Cannot call bluff before bid');
    }

    $this->recordThat(new BluffWasCalled($player));
    $this->recordGameOverOrContinue();

    return $this;
  }

  public function playerCalledSpotOn(string $player): self
  {
    if ($this->gameOver) {
      throw new GameException('Game is over');
    }
    if ($this->diceValues === null) {
      throw new GameException('Game not created');
    }
    if ($this->lastBidQuantity === null || $this->lastBidFace === null) {
      throw new GameException('Cannot call bluff before bid');
    }

    $this->recordThat(new SpotOnWasCalled($player));
    $this->recordGameOverOrContinue();

    return $this;
  }

  public function gameEnded(string $player): self
  {
    if (!$this->gameOver) {
      throw new GameException('Game is not over');
    }
    if ($this->diceValues === null) {
      throw new GameException('Game not created');
    }

    $this->recordThat(new GameEnded($player));

    return $this;
  }

  public function applyGameWasCreated(GameWasCreated $event): void
  {

    $this->nextPlayer = $event->getFirstPlayer();
    $this->diceValues = array_fill_keys($event->getPlayers(), []);
    $this->diceCount = array_fill_keys($event->getPlayers(), 5);
  }

  public function applyDiceWereRerolled(DiceWereRerolled $event): void
  {
    $this->diceValues = $event->getDice();
  }

  public function applyBidWasMade(BidWasMade $event): void
  {
    $this->lastBidQuantity = $event->getQuantity();
    $this->lastBidFace = $event->getFace();
    $this->goToNextPlayer();
  }

  public function applyBluffWasCalled(BluffWasCalled $event): void
  {
    $diceCount = 0;
    foreach ($this->diceValues as $player => $values) {
      $diceCount += count(array_filter($values, fn($value) => $value === $this->lastBidFace));
    }

    if ($diceCount < $this->lastBidQuantity) {
      $this->playerLosesDice($this->lastPlayer);
    } else {
      $this->playerLosesDice($event->getPlayer());
    }
  }

  public function applySpotOnWasCalled(SpotOnWasCalled $event): void
  {
    $diceCount = 0;
    foreach ($this->diceValues as $player => $values) {
      $diceCount += count(array_filter($values, fn($value) => $value === $this->lastBidFace));
    }

    if ($diceCount == $this->lastBidQuantity) {
      $this->allOtherPlayersLoseDice($event->getPlayer());
    } else {
      $this->playerLosesDice($event->getPlayer());
    }
  }

  public function applyGameEnded(GameEnded $event): void
  {
    $this->winner = $event->getWinner();
  }

  private function playerLosesDice($player): void
  {
    $this->diceCount[$player] -= 1;
    if ($this->isGameOver()) {
      $this->endGame();
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
    foreach ($this->diceCount as $otherPlayer => $count) {
      if ($otherPlayer !== $player && $count > 0) {
        $this->diceCount[$otherPlayer] -= 1;
      }
    }
    if ($this->isGameOver()) {
      $this->endGame();
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
    }, ARRAY_FILTER_USE_BOTH));

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

  private function rerollDiceAndRecord(): void
  {
    $newDiceRoll = $this->rollDicePerPlayer($this->diceCount);
    $this->recordThat(new DiceWereRerolled($newDiceRoll));
  }

  private function endGame(): void
  {
    $this->gameOver = true;
    Game::where('uuid', $this->uuid())->update(['completed_at' => now()]);
  }

  private function recordGameOverOrContinue(): void
  {
    if ($this->isGameOver()) {
      $winner = array_keys(array_filter($this->diceCount, fn($count) => $count > 0))[0];
      $this->recordThat(new GameEnded($winner));
    } else {
      $this->rerollDiceAndRecord();
    }
  }

  private function rollDicePerPlayer(array $players): array
  {
    $rolls = [];
    foreach ($players as $player => $diceRemaining) {
      for ($i = 0; $i < $diceRemaining; $i++) {
        $rolls[$player][] = random_int(1, 6);
      }
    }
    return $rolls;
  }
}
