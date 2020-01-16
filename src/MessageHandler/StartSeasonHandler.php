<?php

namespace App\MessageHandler;

use App\Entity\Season;
use App\Entity\Server;
use App\Message\StartSeason;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class StartSeasonHandler
 *
 * @package App\MessageHandler
 */
class StartSeasonHandler implements MessageHandlerInterface
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
     * StartSeasonHandler constructor.
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
     * @param StartSeason $message
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function __invoke(StartSeason $message)
    {
        /**
         * @var Server $server
         * @var Season $season
         */
        $server = $this->entityManager->getRepository(Server::class)->find($message->getServerId());
        $season = $this->entityManager->getRepository(Season::class)->find($message->getSeasonId());

        $this->seasonService->generateFakePlayers($server);
        $this->seasonService->generateSchedule($server, $season);
        $season->setStatus(Season::STATUS_ACTIVE);

        $this->entityManager->flush();
    }
}
