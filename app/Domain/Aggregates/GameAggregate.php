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
  const STARTING_DICE_COUNT = 5;

  private ?string $nextPlayer = null;
  private ?string $lastPlayer = null;
  private ?int $lastBidQuantity = null;
  private ?int $lastBidFace = null;
  private ?array $diceValues = null;
  private ?array $diceCount = null;
  private bool $gameOver = false;
  private ?string $winner = null;

  ///
  /// Record new events
  ///

  /**
   * Creates a new game with the specified players and sets the first player.
   *
   * @param array $players An array of players participating in the game.
   * @param string $firstPlayer The name or identifier of the first player.
   * @return self Returns the instance of the GameAggregate with the new game created.
   */

  public function createGame(array $players, string $firstPlayer): self
  {
    if ($this->nextPlayer !== null) {
      throw new GameException('Game already created');
    }

    $this->recordThat(new GameWasCreated($players, $firstPlayer));
    $this->rerollDiceAndRecord();

    return $this;
  }

  /**
   * Rerolls the dice for the specified players.
   *
   * @param array $dice An array of dice values for each player.
   * @return self Returns the instance of the GameAggregate with the dice rerolled.
   */
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

  /**
   * Makes a bid for the specified player.
   *
   * @param string $player The name or identifier of the player making the bid.
   * @param int $quantity The quantity of dice the player is bidding.
   * @param int $face The face value of the dice the player is bidding.
   * @return self Returns the instance of the GameAggregate with the bid made.
   */
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

  /**
   * Calls bluff on the last bid made.
   *
   * @param string $player The name or identifier of the player calling bluff.
   * @return self Returns the instance of the GameAggregate with the bluff called.
   */
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

  /**
   * Calls spot on on the last bid made.
   *
   * @param string $player The name or identifier of the player calling spot on.
   * @return self Returns the instance of the GameAggregate with the spot on called.
   */
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

  /**
   * Ends the game and declares the winner.
   *
   * @param string $player The name or identifier of the winning player.
   * @return self Returns the instance of the GameAggregate with the game ended.
   */
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

  /**
   * Rerolls the dice for players and records the event.
   *
   * @return void
   */
  private function rerollDiceAndRecord(): void
  {
    $newDiceRoll = $this->rollDicePerPlayer($this->diceCount);
    $this->recordThat(new DiceWereRerolled($newDiceRoll));
  }

  /**
   * Rolls the dice for each player.
   *
   * @param array $players An array of players and the number of dice they have.
   * @return array Returns an array of dice rolls for each player.
   */
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

  /**
   * Records the game over event if the game is over, otherwise rerolls the dice and records the event.
   *
   * @return void
   */
  private function recordGameOverOrContinue(): void
  {
    if ($this->isGameOver()) {
      $winner = array_keys(array_filter($this->diceCount, fn($count) => $count > 0))[0];
      $this->recordThat(new GameEnded($winner));
    } else {
      $this->rerollDiceAndRecord();
    }
  }

  //////////////////////////////////////////////////////////////////////////////
  ///                                                                        ///
  /// Retrieve recorded events and apply them to the aggregate state         ///
  ///                                                                        ///
  //////////////////////////////////////////////////////////////////////////////

  /**
   * Applies the GameWasCreated event to the aggregate state.
   *
   * @param GameWasCreated $event The event to apply.
   */
  public function applyGameWasCreated(GameWasCreated $event): void
  {

    $this->nextPlayer = $event->getFirstPlayer();
    $this->diceValues = array_fill_keys($event->getPlayers(), []);
    $this->diceCount = array_fill_keys($event->getPlayers(), self::STARTING_DICE_COUNT);
  }

  /**
   * Applies the DiceWereRerolled event to the aggregate state.
   *
   * @param DiceWereRerolled $event The event to apply.
   */
  public function applyDiceWereRerolled(DiceWereRerolled $event): void
  {
    $this->diceValues = $event->getDice();
  }

  /**
   * Applies the BidWasMade event to the aggregate state.
   *
   * @param BidWasMade $event The event to apply.
   */
  public function applyBidWasMade(BidWasMade $event): void
  {
    $this->lastBidQuantity = $event->getQuantity();
    $this->lastBidFace = $event->getFace();
    $this->goToNextPlayer();
  }

  /**
   * Applies the BluffWasCalled event to the aggregate state.
   *
   * @param BluffWasCalled $event The event to apply.
   */
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

  /**
   * Applies the SpotOnWasCalled event to the aggregate state.
   *
   * @param SpotOnWasCalled $event The event to apply.
   */
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

  /**
   * Applies the GameEnded event to the aggregate state.
   *
   * @param GameEnded $event The event to apply.
   */
  public function applyGameEnded(GameEnded $event): void
  {
    $this->winner = $event->getWinner();
  }

  /**
   * Handles the logic for when a player loses a dice.
   *
   * @param Player $player The player who loses a dice.
   *
   * @return void
   */
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

  /**
   * Handles the logic for when all other players lose a dice.
   *
   * @param Player $player The player who called spot on.
   *
   * @return void
   */
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

  /**
   * Handles the logic for moving to the next player.
   *
   * @return void
   */
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

  /**
   * Checks if the game is over.
   *
   * @return bool Returns true if the game is over, false otherwise.
   */
  private function isGameOver(): bool
  {
    $remainingPlayers = array_filter($this->diceCount, fn($count) => $count > 0);
    if (count($remainingPlayers) > 1) {
      return false;
    }
    return true;
  }

  /**
   * Ends the game and updates the Game record.
   *
   * @return void
   */
  private function endGame(): void
  {
    $this->gameOver = true;
    Game::where('uuid', $this->uuid())->update(['completed_at' => now()]);
  }
}
