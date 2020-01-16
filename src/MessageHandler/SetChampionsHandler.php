<?php

namespace App\MessageHandler;

use App\Entity\PlayerAward;
use App\Entity\Season;
use App\Entity\TeamAward;
use App\Message\SetChampionAwardToPlayer;
use App\Message\SetChampions;
use App\Repository\TeamStatusRepository;
use App\Utils\Award;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class SetChampionsHandler
 *
 * @package App\MessageHandler
 */
class SetChampionsHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TeamStatusRepository
     */
    private $teamStatusRepository;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * SetChampionsHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TeamStatusRepository $teamStatusRepository
     * @param MessageBusInterface $messageBus
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TeamStatusRepository $teamStatusRepository,
        MessageBusInterface $messageBus
    ) {
        $this->entityManager = $entityManager;
        $this->teamStatusRepository = $teamStatusRepository;
        $this->messageBus = $messageBus;
    }

    /**
     * @param SetChampions $message
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function __invoke(SetChampions $message)
    {
        /**
         * @var Season $season
         */
        $season = $this->entityManager->getRepository(Season::class)->find($message->getSeasonId());

        if($season instanceof Season) {
            $award = new TeamAward();
            $teamStatus = $this->teamStatusRepository->getChampions($season);

            if($this->teamStatusRepository->championsCheck($season, $teamStatus) > 0) {
                $teamStatus = $this->teamStatusRepository->getChampionsByPoints($season, $teamStatus);
            }

            $award
                ->setTeam($teamStatus->getTeam())
                ->setSeason($season)
                ->setAward(Award::TEAM_CHAMPIONS)
            ;

            $this->entityManager->persist($award);
            $this->entityManager->flush();

            $this->messageBus->dispatch(new SetChampionAwardToPlayer($teamStatus->getTeam()->getId(), $season->getId()));
        }
    }
}
