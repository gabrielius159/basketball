<?php

namespace App\Message;

/**
 * Class SetChampions
 *
 * @package App\Message
 */
class SetChampions
{
    /**
     * @var int
     */
    private $seasonId;

    /**
     * SetChampions constructor.
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
