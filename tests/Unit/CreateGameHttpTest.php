<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class CreateGameHttpTest extends TestCase
{
  public function tearDown(): void
  {
    parent::tearDown();
    \Mockery::close();
  }

  #[Test]
  public function it_creates_a_game_on_submission()
  {
    $this->mock(\Spatie\EventSourcing\Commands\CommandBus::class, function (MockInterface $mock) {
      $mock->shouldReceive('dispatch')->once();
    });

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

  #[Test]
  public function it_requires_at_least_two_players()
  {
    $this->mock(\Spatie\EventSourcing\Commands\CommandBus::class, function (MockInterface $mock) {
      $mock->shouldNotReceive('dispatch');
    });

    // Simulate a request to create a new game with only one player
    $response = $this->post('/api/games', [
      'players' => [
        'Han',
      ],
    ]);

    // Assert that the response status is 422 (Unprocessable Entity)
    $response->assertStatus(422);
  }

  #[Test]
  public function it_requires_a_maximum_of_four_players()
  {
    $this->mock(\Spatie\EventSourcing\Commands\CommandBus::class, function (MockInterface $mock) {
      $mock->shouldNotReceive('dispatch');
    });

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
