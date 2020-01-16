<?php

namespace App\Model;

use App\Entity\Team;

/**
 * Class Game
 *
 * @package App\Model
 */
class Game
{
    /**
     * @var Team
     */
    private $teamOne;

    /**
     * @var Team
     */
    private $teamTwo;

    /**
     * Game constructor.
     *
     * @param Team $teamOne
     * @param Team $teamTwo
     */
    public function __construct(Team $teamOne, Team $teamTwo)
    {
        $this->teamOne = $teamOne;
        $this->teamTwo = $teamTwo;
    }

    /**
     * @return Team
     */
    public function getTeamOne(): Team
    {
        return $this->teamOne;
    }

    /**
     * @param Team $teamOne
     */
    public function setTeamOne(Team $teamOne): void
    {
        $this->teamOne = $teamOne;
    }

    /**
     * @return Team
     */
    public function getTeamTwo(): Team
    {
        return $this->teamTwo;
    }

    /**
     * @param Team $teamTwo
     */
    public function setTeamTwo(Team $teamTwo): void
    {
        $this->teamTwo = $teamTwo;
    }
}
