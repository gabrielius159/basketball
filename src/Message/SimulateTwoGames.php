<?php

namespace App\Message;

/**
 * Class SimulateTwoGames
 *
 * @package App\Message
 */
class SimulateTwoGames
{
    /**
     * @var int
     */
    protected $seasonId;

    public function __construct(int $seasonId)
    {
        $this->seasonId = $seasonId;
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
