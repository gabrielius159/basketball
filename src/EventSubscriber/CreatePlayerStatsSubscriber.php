<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Player;
use App\Entity\PlayerStats;
use App\Event\CreatePlayerStatsEvent;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreatePlayerStatsSubscriber implements EventSubscriberInterface
{
    const BATCH_SIZE = 50;

    private $entityManager;
    private $playerRepository;

    /**
     * CreatePlayerStatsSubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param PlayerRepository       $playerRepository
     */
    public function __construct(EntityManagerInterface $entityManager, PlayerRepository $playerRepository)
    {
        $this->entityManager = $entityManager;
        $this->playerRepository = $playerRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CreatePlayerStatsEvent::NAME => [
                ['createPlayerStats']
            ]
        ];
    }

    /**
     * @param CreatePlayerStatsEvent $event
     */
    public function createPlayerStats(CreatePlayerStatsEvent $event): void
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        $i = 1;
        $season = $event->getSeason();
        $server = $season->getServer();

        $playerQuery = $this->playerRepository->findPlayerByServer($server, true);
        $iterableResult = $playerQuery->iterate();

        foreach ($iterableResult as $row) {
            $player = $row[0];

            $newPlayerStats = (new PlayerStats())
                ->setSeason($season)
                ->setPlayer($player)
                ->setSteals(0)
                ->setRebounds(0)
                ->setPoints(0)
                ->setBlocks(0)
                ->setAssists(0)
                ->setGamesPlayed(0);

            $this->entityManager->persist($newPlayerStats);

            if (($i % self::BATCH_SIZE) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            ++$i;
        }

        $this->entityManager->flush();
    }
}
