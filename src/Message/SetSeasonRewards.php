<?php

namespace App\Message;

/**
 * Class SetSeasonRewards
 *
 * @package App\Message
 */
class SetSeasonRewards
{
    /**
     * @var int
     */
    private $seasonId;

    /**
     * SetSeasonRewards constructor.
     *
     * @param int $seasonId
     */
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
