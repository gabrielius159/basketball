<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Player;
use App\Entity\Team;
use Symfony\Contracts\EventDispatcher\Event;

class SetPlayerJerseyNumberEvent extends Event
{
    const NAME = 'set_player_jersey_number';

    private $player;
    private $team;

    /**
     * SetPlayerJerseyNumberEvent constructor.
     *
     * @param Player $player
     * @param Team   $team
     */
    public function __construct(Player $player, Team $team)
    {
        $this->player = $player;
        $this->team   = $team;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }
}
