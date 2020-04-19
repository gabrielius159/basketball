<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Player;
use App\Repository\PlayerStatsRepository;

class PlayerStatsService
{
    private $playerStatsRepository;

    /**
     * PlayerStatsService constructor.
     *
     * @param PlayerStatsRepository $playerStatsRepository
     */
    public function __construct(PlayerStatsRepository $playerStatsRepository)
    {
        $this->playerStatsRepository = $playerStatsRepository;
    }

    /**
     * @param Player $player
     *
     * @return array|null
     */
    public function getCareerPlayerStats(Player $player): ?array
    {
        return $this->playerStatsRepository->getCareerPlayerStats($player);
    }
}
