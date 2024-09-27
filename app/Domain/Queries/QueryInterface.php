<?php

namespace App\Domain\Queries;

interface QueryInterface
{
  public function getGameUuid(): string;
}
