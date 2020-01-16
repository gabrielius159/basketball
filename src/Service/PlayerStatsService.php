<?php

namespace App\Service;

use App\Entity\Player;
use App\Entity\PlayerStats;
use App\Entity\Season;
use App\Repository\PlayerStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class PlayerStatsService
 *
 * @package App\Service
 */
class PlayerStatsService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SeasonService
     */
    private $seasonService;

    /**
     * @var PlayerStatsRepository
     */
    private $playerStatsRepository;

    /**
     * PlayerStatsService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SeasonService $seasonService
     * @param PlayerStatsRepository $playerStatsRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SeasonService $seasonService,
        PlayerStatsRepository $playerStatsRepository
    ) {
        $this->entityManager = $entityManager;
        $this->seasonService = $seasonService;
        $this->playerStatsRepository = $playerStatsRepository;
    }

    /**
     * @param Player $player
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createPlayerStatsOnPlayerCreate(Player $player)
    {
        if($this->seasonService->checkIfActiveSeasonExists($player->getServer())) {
            $playerStats = new PlayerStats();

            $playerStats->setGamesPlayed(0);
            $playerStats->setAssists(0);
            $playerStats->setBlocks(0);
            $playerStats->setPlayer($player);
            $playerStats->setPoints(0);
            $playerStats->setRebounds(0);
            $playerStats->setSteals(0);
            $playerStats->setSeason($this->seasonService->getActiveSeason($player->getServer()));

            $this->entityManager->persist($playerStats);
            $this->entityManager->flush();
        } else {
            $this->seasonService->createNewSeasonWithoutReturn($player->getServer());
        }
    }

    /**
     * @param Player $player
     * @param Season $season
     *
     * @return PlayerStats
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPlayerStatsBySeason(Player $player, Season $season): PlayerStats
    {
        /**
         * @var PlayerStats $playerStats
         */
        $playerStats = $this->entityManager->getRepository(PlayerStats::class)->findOneBy([
           'season' => $season,
           'player' => $player
        ]);

        if(!$playerStats) {
            self::createPlayerStatsOnPlayerCreate($player);

            $playerStats = $this->entityManager->getRepository(PlayerStats::class)->findOneBy([
                'season' => $season,
                'player' => $player
            ]);
        }

        return $playerStats;
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
