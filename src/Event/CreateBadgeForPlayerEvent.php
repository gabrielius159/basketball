<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Badge;
use App\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class CreateBadgeForPlayerEvent extends Event
{
    const NAME = 'create_badge_for_player';

    private $player;
    private $badge;

    /**
     * CreateBadgeForPlayerEvent constructor.
     *
     * @param Player $player
     * @param Badge  $badge
     */
    public function __construct(Player $player, Badge $badge)
    {
        $this->player = $player;
        $this->badge  = $badge;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return Badge
     */
    public function getBadge(): Badge
    {
        return $this->badge;
    }
}