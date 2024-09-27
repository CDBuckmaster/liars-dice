<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class CreatePlayerActionHttpTest extends TestCase
{
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

    // Simulate a request to create a new game
    $response = $this->post('/api/games/actions', [
      'player' => 'Han',
      'game_uuid' => '37ffb296-3a81-4b98-ad07-07f79822436a',
      'action' => 'make_bid',
      'arguments' => [
        'quantity' => 2,
        'face' => 4,
      ]
    ]);

    // Assert that the response status is 201 (Created)
    $response->assertJson(["uuid" => "37ffb296-3a81-4b98-ad07-07f79822436a"]);
  }

  #[Test]
  public function it_requires_quantity_for_bid_actions()
  {
    $this->mock(\Spatie\EventSourcing\Commands\CommandBus::class, function (MockInterface $mock) {
      $mock->shouldNotReceive('dispatch');
    });

    // Simulate a request to create a new game
    $response = $this->post('/api/games/actions', [
      'player' => 'Han',
      'game_uuid' => '37ffb296-3a81-4b98-ad07-07f79822436a',
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

    // Simulate a request to create a new game
    $response = $this->post('/api/games/actions', [
      'player' => 'Han',
      'game_uuid' => '37ffb296-3a81-4b98-ad07-07f79822436a',
      'action' => 'nonsense',
    ]);

    // Assert that the response status is 422 (Unprocessable Entity)
    $response->assertStatus(422);
  }
}
