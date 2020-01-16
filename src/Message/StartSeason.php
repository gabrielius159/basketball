<?php

namespace App\Message;

/**
 * Class StartSeason
 *
 * @package App\Message
 */
class StartSeason
{
    /**
     * @var int
     */
    private $serverId;

    /**
     * @var int
     */
    private $seasonId;

    /**
     * StartSeason constructor.
     *
     * @param int $serverId
     * @param int $seasonId
     */
    public function __construct(int $serverId, int $seasonId)
    {
        $this->serverId = $serverId;
        $this->seasonId = $seasonId;
    }

    /**
     * @return int
     */
    public function getServerId(): int
    {
        return $this->serverId;
    }

    /**
     * @param int $serverId
     */
    public function setServerId(int $serverId): void
    {
        $this->serverId = $serverId;
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
