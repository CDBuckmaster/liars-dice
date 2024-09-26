<?php

namespace App\Domain\Commands;

interface CommandInterface
{
  public function getGameUuid(): string;
}
