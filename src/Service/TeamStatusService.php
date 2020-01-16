<?php

namespace App\Service;

use App\Entity\Team;
use App\Entity\TeamStatus;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TeamStatusService
 *
 * @package App\Service
 */
class TeamStatusService
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
     * TeamStatusService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SeasonService $seasonService
     */
    public function __construct(EntityManagerInterface $entityManager, SeasonService $seasonService)
    {
        $this->entityManager = $entityManager;
        $this->seasonService = $seasonService;
    }

    /**
     * @param Team $team
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createTeamStatusOnTeamCreate(Team $team)
    {
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
