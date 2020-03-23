<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\TeamStatus;
use App\Event\CreateTeamStatusEvent;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateTeamStatusSubscriber implements EventSubscriberInterface
{
    const BATCH_SIZE = 50;

    private $entityManager;
    private $teamRepository;

    /**
     * CreateTeamStatusSubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TeamRepository         $teamRepository
     */
    public function __construct(EntityManagerInterface $entityManager, TeamRepository $teamRepository)
    {
        $this->entityManager = $entityManager;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CreateTeamStatusEvent::NAME => [
                ['createTeamStatuses']
            ]
        ];
    }

    /**
     * @param CreateTeamStatusEvent $event
     */
    public function createTeamStatuses(CreateTeamStatusEvent $event): void
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        $i = 1;
        $season = $event->getSeason();
        $server = $season->getServer();

        $teamsQuery = $this->teamRepository->findTeamsByServer($server, true);
        $iterableResult = $teamsQuery->iterate();

        foreach ($iterableResult as $row) {
            $team = $row[0];

            $newTeamStats = (new TeamStatus())
                ->setSeason($season)
                ->setTeam($team)
                ->setLose(0)
                ->setPoints(0)
                ->setWin(0)
            ;

            $this->entityManager->persist($newTeamStats);

            if (($i % self::BATCH_SIZE) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear(TeamStatus::class);
            }

            ++$i;
        }

        $this->entityManager->flush();
    }
}
