<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\TeamStatus;
use App\Event\CreateNewTeamTeamStatusEvent;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateNewTeamTeamStatusSubscriber implements EventSubscriberInterface
{
    private $seasonService;
    private $entityManager;

    /**
     * CreateNewTeamTeamStatusSubscriber constructor.
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
            CreateNewTeamTeamStatusEvent::NAME => [
                ['createNewTeamTeamStatus']
            ]
        ];
    }

    /**
     * @param CreateNewTeamTeamStatusEvent $event
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createNewTeamTeamStatus(CreateNewTeamTeamStatusEvent $event): void
    {
        $team = $event->getTeam();

        if($this->seasonService->checkIfActiveSeasonExists($team->getServer())) {
            $teamStatus = (new TeamStatus())
                ->setTeam($team)
                ->setWin(0)
                ->setLose(0)
                ->setPoints(0)
                ->setSeason($this->seasonService->getActiveSeason($team->getServer()));

            $this->entityManager->persist($teamStatus);
            $this->entityManager->flush();
        } else {
            $this->seasonService->createNewSeasonWithoutReturn($team->getServer());
        }
    }
}