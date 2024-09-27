<?php

namespace App\Domain\Commands;

final class PlayerActionCommandFactory
{
  const CALL_BLUFF_ACTION_TYPE = 'call_bluff';
  const CALL_SPOT_ON_ACTION_TYPE = 'call_spot_on';
  const MAKE_BID_ACTION_TYPE = 'make_bid';

  public static $actionTypes = [
    self::CALL_BLUFF_ACTION_TYPE,
    self::CALL_SPOT_ON_ACTION_TYPE,
    self::MAKE_BID_ACTION_TYPE,
  ];

  public static function create(string $gameUuid, string $actionType, string $playerName, array $arguments = []): PlayerActionCommand
  {
    switch ($actionType) {
      case 'call_bluff':
        return new CallBluffCommand($gameUuid, $playerName);
      case 'call_spot_on':
        return new CallSpotOnCommand($gameUuid, $playerName);
      case 'make_bid':
        if (!isset($arguments['quantity']) || !isset($arguments['face'])) {
          throw new \InvalidArgumentException('Quantity and face are required for make_bid action');
        }
        return new MakeBidCommand($gameUuid, $playerName, $arguments['quantity'], $arguments['face']);
      default:
        throw new \InvalidArgumentException("Invalid action type: $actionType");
    }
  }
}
