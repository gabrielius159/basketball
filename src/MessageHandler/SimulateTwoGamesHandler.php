<?php

namespace App\MessageHandler;

use App\Message\SimulateTwoGames;
use App\Repository\GameDayRepository;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class SimulateTwoGamesHandler
 *
 * @package App\MessageHandler
 */
class SimulateTwoGamesHandler implements MessageHandlerInterface
{
    /**
     * @var GameDayRepository
     */
    private $gameDayRepository;

    /**
     * @var SeasonService
     */
    private $seasonService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * SimulateTwoGamesHandler constructor.
     *
     * @param GameDayRepository $gameDayRepository
     * @param SeasonService $seasonService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        GameDayRepository $gameDayRepository,
        SeasonService $seasonService,
        EntityManagerInterface $entityManager
    ) {

        $this->gameDayRepository = $gameDayRepository;
        $this->seasonService = $seasonService;
        $this->entityManager = $entityManager;
    }

    /**
     * @param SimulateTwoGames $message
     *
     * @throws \Exception
     */
    public function __invoke(SimulateTwoGames $message)
    {
        $gameDays = $this->gameDayRepository->getTwoUpcomingGamesWithSeasonId($message->getSeasonId());

        if($gameDays) {
            foreach ($gameDays as $gameDay) {
                $this->seasonService->playGame($gameDay);
            }
        }

        $this->entityManager->clear();
    }
}
