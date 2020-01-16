<?php

namespace App\Message;

/**
 * Class CheckContracts
 *
 * @package App\Message
 */
class CheckContracts
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
     * CheckContracts constructor.
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
