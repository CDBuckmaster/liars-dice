<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreatePlayerActionHttpTest extends TestCase
{
  use RefreshDatabase;

  public function setup(): void
  {
    parent::setUp();
    // I know it's no longer a unit test by any means, but the route level binding is messing with things
    Game::create([
      'uuid' => '37ffb296-3a81-4b98-ad07-07f79822436a',
      'metadata' => [
        'players' => ['Han', 'Luke'],
        'current_player' => 'Han',
      ],
    ]);
  }

  public function tearDown(): void
  {
    parent::tearDown();
    \Mockery::close();
  }

  #[Test]
  public function it_can_create_player_actions()
  {
    $this->mock(\Spatie\EventSourcing\Commands\CommandBus::class, function (MockInterface $mock) {
      $mock->shouldReceive('dispatch')->once();
    });

    // Simulate a request to create a player action
    $response = $this->post('/api/games/37ffb296-3a81-4b98-ad07-07f79822436a/actions', [
      'player' => 'Han',
      'action' => 'make_bid',
      'arguments' => [
        'quantity' => 2,
        'face' => 4,
      ]
    ]);

    // Assert that the response status is 201 (Created)
    $response->assertStatus(201);
    $response->assertJson(["uuid" => "37ffb296-3a81-4b98-ad07-07f79822436a"]);
  }

  #[Test]
  public function it_requires_quantity_for_bid_actions()
  {
    $this->mock(\Spatie\EventSourcing\Commands\CommandBus::class, function (MockInterface $mock) {
      $mock->shouldNotReceive('dispatch');
    });

    // Simulate a request to create a player action
    $response = $this->post('/api/games/37ffb296-3a81-4b98-ad07-07f79822436a/actions', [
      'player' => 'Han',
      'action' => 'make_bid',
      'arguments' => [
        'face' => 4,
      ]
    ]);

    // Assert that the response status is 422 (Unprocessable Entity)
    $response->assertStatus(422);
  }

  #[Test]
  public function it_requires_valid_action_type()
  {
    $this->mock(\Spatie\EventSourcing\Commands\CommandBus::class, function (MockInterface $mock) {
      $mock->shouldNotReceive('dispatch');
    });

    // Simulate a request to create a player action
    $response = $this->post('/api/games/37ffb296-3a81-4b98-ad07-07f79822436a/actions', [
      'player' => 'Han',
      'action' => 'nonsense',
    ]);

    // Assert that the response status is 422 (Unprocessable Entity)
    $response->assertStatus(422);
  }
}
