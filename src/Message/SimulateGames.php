<?php

namespace App\Message;

/**
 * Class SimulateGames
 *
 * @package App\Message
 */
class SimulateGames
{
    /**
     * @var int
     */
    protected $seasonId;

    /**
     * @var int
     */
    private $serverId;

    public function __construct(int $seasonId, int $serverId)
    {
        $this->seasonId = $seasonId;
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
}
