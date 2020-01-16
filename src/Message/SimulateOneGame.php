<?php declare(strict_types=1);

namespace App\Message;

/**
 * Class SimulateOneGame
 *
 * @package App\Message
 */
class SimulateOneGame
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