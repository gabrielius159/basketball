<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Team;
use Symfony\Contracts\EventDispatcher\Event;

class CreateNewTeamTeamStatusEvent extends Event
{
    const NAME = 'create_new_team_team_status';

    private $team;

    /**
     * CreateNewTeamTeamStatusEvent constructor.
     *
     * @param Team $team
     */
    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }
}