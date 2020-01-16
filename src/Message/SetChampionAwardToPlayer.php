<?php

namespace App\Message;

/**
 * Class SetChampionAwardToPlayer
 *
 * @package App\Message
 */
class SetChampionAwardToPlayer
{
    /**
     * @var int
     */
    private $teamId;

    /**
     * @var int
     */
    private $seasonId;

    /**
     * SetChampionAwardToPlayer constructor.
     *
     * @param int $teamId
     * @param int $seasonId
     */
    public function __construct(int $teamId, int $seasonId)
    {
        $this->teamId = $teamId;
        $this->seasonId = $seasonId;
    }

    /**
     * @return int
     */
    public function getTeamId(): int
    {
        return $this->teamId;
    }

    /**
     * @param int $teamId
     */
    public function setTeamId(int $teamId): void
    {
        $this->teamId = $teamId;
    }

    /**
     * @return int
     */
    public function getSeasonId(): int
    {
        return $this->seasonId;
    }

    /**
     * @param int $seasonId
     */
    public function setSeasonId(int $seasonId): void
    {
        $this->seasonId = $seasonId;
    }
}
