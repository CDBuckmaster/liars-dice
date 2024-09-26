<?php

namespace App\Domain\Commands;

abstract class PlayerActionCommand
{
  protected string $gameUuid;
  protected string $playerName;

  /**
   * PlayerActionCommand constructor.
   *
   * @param string $gameUuid
   * @param string $playerName
   */
  public function __construct(string $gameUuid, string $playerName)
  {
    $this->gameUuid = $gameUuid;
    $this->playerName = $playerName;
  }

  public function getGameUuid(): string
  {
    return $this->gameUuid;
  }

  public function getPlayerName(): string
  {
    return $this->playerName;
  }
}
