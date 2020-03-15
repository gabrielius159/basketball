<?php declare(strict_types=1);

namespace App\Service\Admin;

use App\Entity\Season;
use App\Form\SeasonStartFormType;
use App\Message\SimulateGames;
use App\Message\SimulateOneGame;
use App\Message\SimulateTwoGames;
use App\Message\StartSeason;
use App\Service\SeasonService;
use Symfony\Component\Messenger\MessageBusInterface;

class SeasonManagementService
{
    const WARNING_KEY = 'warning';
    const SUCCESS_KEY = 'success';

    const MESSAGE_CHOICE_NOT_FOUND = 'Action was not found.';
    const MESSAGE_SIMULATION_STARTED = 'Simulation started.';
    const MESSAGE_SIMULATED_TWO_GAMES = 'You successfully simulated two games.';
    const MESSAGE_SIMULATED_ONE_GAME = 'You successfully simulated one game.';
    const MESSAGE_NO_TEAMS = 'To start season, you have to create two teams.';
    const MESSAGE_NO_COACH = 'There is teams that doesn\'t have coach.';
    const MESSAGE_SEASON_STARTED = 'You successfully started season.';

    private $messageBus;
    private $seasonService;

    /**
     * SeasonManagementService constructor.
     *
     * @param MessageBusInterface $messageBus
     * @param SeasonService       $seasonService
     */
    public function __construct(MessageBusInterface $messageBus, SeasonService $seasonService)
    {
        $this->messageBus = $messageBus;
        $this->seasonService = $seasonService;
    }

    /**
     * @param int    $choice
     * @param Season $season
     *
     * @return array
     */
    public function dispatchChoosenActionAndReturnMessage(int $choice, Season $season): array
    {
        switch($choice) {
            case SeasonStartFormType::SIMULATE_SEASON: {
                $this->messageBus->dispatch(new SimulateGames($season->getId(), $season->getServer()->getId()));

                return [self::SUCCESS_KEY, self::MESSAGE_SIMULATION_STARTED];
            }
            case SeasonStartFormType::SIMULATE_TWO_GAME: {
                $this->messageBus->dispatch(new SimulateTwoGames($season->getId()));

                return [self::SUCCESS_KEY, self::MESSAGE_SIMULATED_TWO_GAMES];
            }
            case SeasonStartFormType::SIMULATE_ONE_GAME: {
                $this->messageBus->dispatch(new SimulateOneGame($season->getId()));

                return [self::SUCCESS_KEY, self::MESSAGE_SIMULATED_ONE_GAME];
            }
            case SeasonStartFormType::START_SEASON: {
                if(count($season->getServer()->getTeams()) <= 1) {
                    return [self::WARNING_KEY, self::MESSAGE_NO_TEAMS];
                }

                if(!$this->seasonService->teamsHaveCoach($season->getServer())) {
                    return [self::WARNING_KEY, self::MESSAGE_NO_COACH];
                }

                $this->messageBus->dispatch(new StartSeason($season->getServer()->getId(), $season->getId()));

                return [self::SUCCESS_KEY, self::MESSAGE_SEASON_STARTED];
            }
        }


        return [self::WARNING_KEY, self::MESSAGE_CHOICE_NOT_FOUND];
    }
}