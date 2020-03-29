<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class CreateNewPlayerPlayerStatsEvent extends Event
{
    const NAME = 'create_new_player_player_stats';

    private $player;

    /**
     * CreateNewPlayerPlayerStatsEvent constructor.
     *
     * @param Player $player
     */
    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }
}