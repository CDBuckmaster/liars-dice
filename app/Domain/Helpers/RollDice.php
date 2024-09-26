<?php

if (! function_exists('rollDice')) {
  function rollDice(int $sides): int
  {
    return random_int(1, $sides);
  }
}

if (! function_exists('rollDicePerPlayer')) {
  function rollDicePerPlayer(array $players): array
  {
    $rolls = [];
    foreach ($players as $player => $diceRemaining) {
      for ($i = 0; $i < $diceRemaining; $i++) {
        $rolls[$player][] = rollDice(6);
      }
    }
    return $rolls;
  }
}
