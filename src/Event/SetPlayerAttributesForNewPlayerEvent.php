<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class SetPlayerAttributesForNewPlayerEvent extends Event
{
    const NAME = 'set_player_attributes_for_new_player';

    private $player;

    /**
     * SetPlayerAttributesForNewPlayerEvent constructor.
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