# liars-dice

Welcome to my project: Liar's Dice implemented with CQRS and Event Sourcing.

The main purpose of this was to be a learning project to get used to working with the CQRS and Event Sourcing patterns. Instead of using the usual use cases like bank transactions or shipping logistics, I thought I'd apply my understanding to a game close to my heart: Liar's Dice.

Brief description of how to play Liar's Dice:

-   Each player has a cup full of dice
-   Everyone shakes their cup, and then looks at their dice secretly
-   Players take it in turns to announce their bids in the form "There are at least x y", where x is number of dice and y is the face value
-   On a player's turn they can choose to raise the previous bid, increasing the number of dice (and saying whatever face value they want), or they can increase the face value instead.
-   Alternatively they can either call the bluff "there's no way there's 5 6s" or they can call spot on "there's exactly 5 6s"
-   If they call a bluff and they're right, the previous player loses a dice, if they're wrong, they are the one to lose a dice
-   If they call spot on and they're right, ALL OTHER players lose a dice, if they're wrong they are the only one to lose a dice
-   After a bluff or spot on is called, everyone rerolls their remaining dice
-   If you lose all of your dice you are out
-   Last player standing wins

This implementation of Event Sourcing relies heavily on [Spatie's Event-Sourcing package](https://spatie.be/docs/laravel-event-sourcing/v7/introduction).

## Installation

Unfortunately I have not yet tested this process, as I scaffolded this project with Laravel Sail directly (seemed like a nice thing to try). I believe the following steps should work:

1. Make sure Docker is installed and running on your local
2. Clone this repo to your local, however you like to do so
3. Run the following:

```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

4. Copy `.env.example` to `.env` and change MySQL settings to whatever you like
5. Run `./vendor/bin/sail/up`
6. Run `./vendor/bin/sail artisan key:generate`
7. Run `./vendor/bin/sail artisan migrate`
8. You should be golden

## API

Postman collection: https://www.postman.com/cdbuckmaster/workspace/public-demos/collection/5308018-5100a28d-b6dd-46b1-9a28-a3a84c5e699c?action=share&creator=5308018

API is fairly simple, routes are divided between write and read endpoints:

### Create a New Game

**Endpoint:** `POST api/games`

**Description:** Creates a new game with the specified players.

**Body:**

```
{
  "players": [
    "string", // Player 1
    "string", // Player 2
    // Optional: Up to 4 players
  ]
}
```

**Response:**

```
{
  "uuid": "string"
}
```

### Make a player action

**Endpoint:** `POST api/games/{game_uuid}/actions`

**Description:** Player makes a game move

**Body:**

```
{
  "action": "string", // Either "make_bid", "call_bluff", "call_spot_on"
  "player": "string", // Player 1
  "arguments": { // Only required for "make_bid" action
    "quantity": int // Number of dice
    "face": int // Face value of dice
  }
}
```

**Response:**

```
{
  "uuid": "string"
}
```

### Get next player

**Endpoint:** `GET api/games/{game_uuid}/next-player`

**Description:** Get the player whose turn it is

**Response:**

```
{
  "player": "string"
}
```

### Get player dice

**Endpoint:** `GET api/games/{game_uuid}/player/{player_name}/dice`

**Description:** Get the current dice rolled for a player

**Response:**

```
{
  "dice": [int]
}
```

### Get dice count

**Endpoint:** `GET api/games/{game_uuid}/dice`

**Description:** Get the number of dice each player has remaining

**Response:**

```
{
  "dice": {
    [player_name]: int
  }
}
```

### Get game status

**Endpoint:** `GET api/games/{game_uuid}/status`

**Description:** Get whether the game is over or not

**Response:**

```
{
  "status": "string" // "completed" or "in progress"
}
```

## Tests

Tests can be run with `./vendor/bin/sail artisan test`.

They aren't complete, the query endpoints don't have tests yet as I need to experiment with a proper way of seeding Spatie's Stored Event table.

## TODO

- [ ] Create a factory for inserting events in DB directly for testing
- [ ] Rewriten HTTP tests to be fully functional, seeding DB with event factory
- [ ] Write functional tests for queries
- [ ] Break down GameAggregate into smaller parts
- [ ] Add additional query endpoints to make better use of Event Sourcing (such as game statistics like who calls most bluffs)
