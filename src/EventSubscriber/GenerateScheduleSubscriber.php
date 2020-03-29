<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\GameDay;
use App\Entity\Season;
use App\Event\GenerateScheduleEvent;
use App\Model\Game;
use App\Repository\TeamRepository;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GenerateScheduleSubscriber implements EventSubscriberInterface
{
    private $entityManager;
    private $seasonService;
    private $teamRepository;
    private $logger;

    /**
     * GenerateScheduleSubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SeasonService          $seasonService
     * @param TeamRepository         $teamRepository
     * @param LoggerInterface        $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SeasonService $seasonService,
        TeamRepository $teamRepository,
        LoggerInterface $logger
    ) {
        $this->entityManager  = $entityManager;
        $this->seasonService  = $seasonService;
        $this->teamRepository = $teamRepository;
        $this->logger         = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            GenerateScheduleEvent::NAME => [
                ['generateSchedule']
            ]
        ];
    }

    /**
     * @param GenerateScheduleEvent $event
     *
     * @throws \Exception
     */
    public function generateSchedule(GenerateScheduleEvent $event): void
    {
        $season = $event->getSeason();
        $server = $season->getServer();

        $teams = $this->teamRepository->findTeamsByServer($server);

        if (count($teams) <= 1) {
            $this->logger->alert('Trying to generate schedule, but there is not enough teams');

            return;
        }

        $matchDay = new \DateTime();
        $matchDay->modify('+1 day');
        $games = $this->groupGames($teams);

        $this->createGameDayEntities($games, $matchDay, $season);
        $season->setStatus(Season::STATUS_ACTIVE);

        $this->entityManager->flush();
    }

    private function createGameDayEntities(array $games, \DateTime $matchDay, Season $season): void
    {
        shuffle($games);

        $numberOfChunks = intval(ceil(count($games) / 1000));
        $chunks = array_chunk($games, $numberOfChunks);

        foreach($chunks as $chunk) {
            /**
             * @var Game $game
             */
            foreach($chunk as $game) {
                $gameDay = (new GameDay())
                    ->setTeamOne($game->getTeamOne())
                    ->setTeamTwo($game->getTeamTwo())
                    ->setTime($matchDay)
                    ->setStatus(GameDay::STATUS_WAITING)
                    ->setSeason($season)
                ;

                $this->entityManager->persist($gameDay);
                $matchDay->modify('+1 day');
            }

            $this->entityManager->flush();
        }
    }

    /**
     * @param array $teams
     *
     * @return array
     */
    private function groupGames(array $teams): array
    {
        $games = [];

        foreach($teams as $teamOne) {
            foreach($teams as $teamTwo) {
                if($teamOne !== $teamTwo) {
                    $games[] = new Game($teamOne, $teamTwo);
                }
            }
        }

        return $games;
    }
}