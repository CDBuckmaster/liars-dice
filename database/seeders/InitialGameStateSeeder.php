<?php

namespace Database\Seeders;

use App\Models\Game;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialGameStateSeeder extends Seeder
{
  const INITIAL_GAME = [
    'uuid' => '37ffb296-3a81-4b98-ad07-07f79822436a',
    'metadata' => [
      'players' => [
        'Han',
        'Leia',
      ],
      'current_player' => 'Han',
    ],
    'completed_at' => null,
  ];
  /**
   * Seed the application's database.
   */
  public function run(): void
  {

    Game::create(self::INITIAL_GAME);

    DB::table('stored_events')->insert(
      [
        'id' => 1,
        'aggregate_uuid' => self::INITIAL_GAME['uuid'],
        'aggregate_version' => 1,
        'event_version' => 1,
        'event_class' => 'App\Domain\Events\GameWasCreated',
        'event_properties' => json_encode([
          'players' => [
            'Han',
            'Leia',
          ],
          'starting_player' => 'Han',
        ]),
        'meta_data' => json_encode([]),
        'created_at' => now(),
      ]
    );

    DB::table('stored_events')->insert(
      [
        'id' => 2,
        'aggregate_uuid' => self::INITIAL_GAME['uuid'],
        'aggregate_version' => 2,
        'event_version' => 1,
        'event_class' => 'App\Domain\Events\DiceWereRerolled',
        'event_properties' => json_encode([
          'dice' => [
            'Han' => [
              3,
              1,
              3,
              6,
              6
            ],
            'Leia' => [
              6,
              1,
              6,
              3,
              6
            ]
          ]
        ]),
        'meta_data' => json_encode([]),
        'created_at' => now(),
      ]
    );
  }
}
