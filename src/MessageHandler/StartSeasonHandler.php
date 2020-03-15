<?php

namespace App\MessageHandler;

use App\Entity\Season;
use App\Entity\Server;
use App\Event\GenerateScheduleEvent;
use App\Message\StartSeason;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class StartSeasonHandler
 *
 * @package App\MessageHandler
 */
class StartSeasonHandler implements MessageHandlerInterface
{
    private $entityManager;
    private $seasonService;
    private $eventDispatcher;

    /**
     * StartSeasonHandler constructor.
     *
     * @param EntityManagerInterface   $entityManager
     * @param SeasonService            $seasonService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SeasonService $seasonService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->seasonService = $seasonService;
        $this->eventDispatcher = $eventDispatcher;
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

        $event = new GenerateScheduleEvent($season);
        $this->eventDispatcher->dispatch($event, GenerateScheduleEvent::NAME);

        $season->setStatus(Season::STATUS_ACTIVE);

        $this->entityManager->flush();
    }
}
