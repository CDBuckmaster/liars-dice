<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class GameCreateTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function it_creates_a_game_on_submission()
  {
    // Simulate a request to create a new game
    $response = $this->post('/api/games', [
      'players' => [
        'Han',
        'Luke',
      ],
    ]);

    // Assert that the response status is 201 (Created)
    $response->assertStatus(201);

    // Assert that a game now exists in the DB
    $this->assertDatabaseCount('games', 1);
  }

  /** @test */
  public function it_requires_at_least_two_players()
  {
    // Simulate a request to create a new game with only one player
    $response = $this->post('/api/games', [
      'players' => [
        'Han',
      ],
    ]);

    // Assert that the response status is 422 (Unprocessable Entity)
    $response->assertStatus(422);

    // Assert that no game exists in the DB
    $this->assertDatabaseCount('games', 0);
  }

  /** @test */
  public function it_requires_a_maximum_of_four_players()
  {
    // Simulate a request to create a new game with five players
    $response = $this->post('/api/games', [
      'players' => [
        'Han',
        'Luke',
        'Leia',
        'Chewbacca',
        'R2-D2',
      ],
    ]);

    // Assert that the response status is 422 (Unprocessable Entity)
    $response->assertStatus(422);

    // Assert that no game exists in the DB
    $this->assertDatabaseCount('games', 0);
  }
}
