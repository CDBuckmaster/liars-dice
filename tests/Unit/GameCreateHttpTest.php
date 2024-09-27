<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;

class GameCreateHttpTest extends TestCase
{
  protected $mock;

  public function tearDown(): void
  {
    parent::tearDown();
    Mockery::close();
  }

  public function setup(): void
  {
    parent::setup();
    $this->instance(
      'Illuminate\Contracts\Bus\Dispatcher',
      Mockery::mock('Illuminate\Contracts\Bus\Dispatcher')
    );

    $this->mock = Mockery::mock('Illuminate\Contracts\Bus\Dispatcher');
    $this->instance('Illuminate\Contracts\Bus\Dispatcher', $this->mock);
  }

  /** @test */
  public function it_creates_a_game_on_submission()
  {
    $this->mock->shouldReceive('dispatch');

    // Simulate a request to create a new game
    $response = $this->post('/api/games', [
      'players' => [
        'Han',
        'Luke',
      ],
    ]);

    // Assert that the response status is 201 (Created)
    $response->assertStatus(201);
  }

  /** @test */
  public function it_requires_at_least_two_players()
  {
    $this->mock->shouldNotReceive('dispatch');

    // Simulate a request to create a new game with only one player
    $response = $this->post('/api/games', [
      'players' => [
        'Han',
      ],
    ]);

    // Assert that the response status is 422 (Unprocessable Entity)
    $response->assertStatus(422);
  }

  /** @test */
  public function it_requires_a_maximum_of_four_players()
  {
    $this->mock->shouldNotReceive('dispatch');

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
  }
}
