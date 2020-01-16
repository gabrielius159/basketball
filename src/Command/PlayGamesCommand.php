<?php

namespace App\Command;

use App\Entity\GameDay;
use App\Entity\Season;
use App\Entity\Server;
use App\Repository\GameDayRepository;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PlayGamesCommand extends Command
{
    protected static $defaultName = 'app:start-season';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SeasonService
     */
    protected $seasonService;

    /**
     * @var GameDayRepository
     */
    protected $gameDayRepository;

    /**
     * PlayGamesCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SeasonService $seasonService
     * @param GameDayRepository $gameDayRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SeasonService $seasonService,
        GameDayRepository $gameDayRepository
    ) {
        $this->entityManager = $entityManager;
        $this->seasonService = $seasonService;
        $this->gameDayRepository = $gameDayRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('This command starts season on specific server.\nIf season already started it will do nothing.')
            ->addArgument('server', InputArgument::REQUIRED, 'Server name')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var Server $server
         */
        $server = $this->entityManager->getRepository(Server::class)->findOneBy([
            'name' => $input->getArgument('server')
        ]);

        $io = new SymfonyStyle($input, $output);

        if(!$server) {
            $io->error('Cannot find specified server!');

            return 1;
        }

        /**
         * @var Season $season
         */
        $season = $this->seasonService->getActiveSeason($server);

        if($season->getStatus() === Season::STATUS_ACTIVE) {
            $today = new \DateTime();

            try {
                $gameDays = $this->gameDayRepository->getByDate($today, $season);

                if($gameDays) {
                    /**
                     * @var GameDay $gameDay
                     */
                    foreach($gameDays as $gameDay) {
                        $teamOne = $gameDay->getTeamOne();
                        $teamTwo = $gameDay->getTeamTwo();

                        // ToDo: Create fake data for players
                        // ToDo: Create new GameDayScores

                    }

                } else {
                    $io->error('There is no games today.');

                    return 1;
                }

            } catch (\Exception $exception) {
                $io->error($exception->getMessage());

                return 1;
            }
        } else {
            $io->error('Season hasn\'t started.');
            return 1;
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
