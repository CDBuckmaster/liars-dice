<?php

namespace Tests\Feature;

use Database\Seeders\InitialGameStateSeeder;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class PlayerActionTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function it_makes_a_valid_opening_move()
  {
    $this->seed(InitialGameStateSeeder::class);

    // Simulate a request to create a new game
    $response = $this->post('/api/games/actions', [
      'player' => 'Han',
      'game_uuid' => InitialGameStateSeeder::INITIAL_GAME['uuid'],
      'action' => 'make_bid',
      'arguments' => [
        'face' => 3,
        'quantity' => 1,
      ],
    ]);

    // Assert that the response status is 201 (Created)
    $response->assertStatus(201);
  }
}
