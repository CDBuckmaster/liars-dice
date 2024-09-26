<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Game extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'metadata',
    'completed_at',
  ];

  protected $attributes = [
    'completed_at' => null,
    'metadata' => null,
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'metadata' => 'object',
      'completed_at' => 'datetime',
    ];
  }
}
