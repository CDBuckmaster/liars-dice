<?php

namespace App\Domain\Commands;

use Illuminate\Support\Str;
use Spatie\EventSourcing\Commands\HandledBy;
use App\Domain\CommandHandlers\CreateGameCommandHandler;

#[HandledBy(CreateGameCommandHandler::class)]
class CreateGameCommand
{
  public string $gameUuid;
  public function __construct(
    public array $players,
  ) {
    $this->gameUuid = (string) Str::uuid();
  }
}
