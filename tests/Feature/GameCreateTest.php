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
        'Chewbacca',
        'Leia',
        'Obi-Wan'
      ],
    ]);

    // Assert that the response status is 201 (Created)
    $response->assertStatus(201);

    // Assert that a game now exists in the DB
    $this->assertDatabaseCount('games', 1);
  }
}
