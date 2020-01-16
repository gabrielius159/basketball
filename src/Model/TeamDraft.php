<?php

namespace App\Model;

use App\Entity\Team;

/**
 * Class TeamDraft
 *
 * @package App\Model
 */
class TeamDraft
{
    /**
     * @var Team $team
     */
    private $team;

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

    /**
     * @param Team $team
     */
    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }
}
