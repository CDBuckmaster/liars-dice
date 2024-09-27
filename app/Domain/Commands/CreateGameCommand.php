<?php

namespace App\Domain\Commands;

use Illuminate\Support\Str;
use Spatie\EventSourcing\Commands\HandledBy;
use App\Domain\Handlers\Commands\CreateGameCommandHandler;

#[HandledBy(CreateGameCommandHandler::class)]
final class CreateGameCommand implements CommandInterface
{
  protected string $gameUuid;
  protected array $players;

  public function __construct(
    array $players,
  ) {
    $this->players = $players;
    $this->gameUuid = (string) Str::uuid();
  }

  public function getGameUuid(): string
  {
    return $this->gameUuid;
  }

  public function getPlayers(): array
  {
    return $this->players;
  }
}
