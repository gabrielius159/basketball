<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\PlayerStats;
use App\Event\CreateNewPlayerPlayerStatsEvent;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateNewPlayerPlayerStatsSubscriber implements EventSubscriberInterface
{
    private $seasonService;
    private $entityManager;

    /**
     * CreateNewPlayerPlayerStatsSubscriber constructor.
     *
     * @param SeasonService          $seasonService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(SeasonService $seasonService, EntityManagerInterface $entityManager)
    {
        $this->seasonService = $seasonService;
        $this->entityManager = $entityManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CreateNewPlayerPlayerStatsEvent::NAME => [
                ['createPlayerPlayerStats']
            ]
        ];
    }

    /**
     * @param CreateNewPlayerPlayerStatsEvent $event
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createPlayerPlayerStats(CreateNewPlayerPlayerStatsEvent $event): void
    {
        $player = $event->getPlayer();

        if ($this->seasonService->checkIfActiveSeasonExists($player->getServer())) {
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
}