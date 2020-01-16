<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\GameDay;
use App\Message\SimulateOneGame;
use App\Repository\GameDayRepository;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SimulateOneGameHandler implements MessageHandlerInterface
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
     * SimulateOneGameHandler constructor.
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
     * @param SimulateOneGame $message
     *
     * @throws \Exception
     */
    public function __invoke(SimulateOneGame $message)
    {
        $gameDay = $this->gameDayRepository->getUpcomingGameWithSeasonId($message->getSeasonId());

        if($gameDay instanceof GameDay) {
            $this->seasonService->playGame($gameDay);
        }

        $this->entityManager->clear();
    }
}