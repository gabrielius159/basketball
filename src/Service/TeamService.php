<?php

namespace App\Service;

use App\Entity\Coach;
use App\Entity\GameDay;
use App\Entity\Player;
use App\Entity\Position;
use App\Entity\Server;
use App\Entity\Team;
use App\Entity\TeamStatus;
use App\Repository\GameDayRepository;
use App\Repository\TeamStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TeamService
 *
 * @package App\Service
 */
class TeamService
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
     * @var TeamStatusRepository
     */
    private $teamStatusRepository;

    /**
     * @var PlayerService
     */
    private $playerService;

    /**
     * @var GameDayRepository
     */
    private $gameDayRepository;

    private $fakePlayerService;

    /**
     * TeamService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SeasonService $seasonService
     * @param TeamStatusRepository $teamStatusRepository
     * @param PlayerService $playerService
     * @param GameDayRepository $gameDayRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SeasonService $seasonService,
        TeamStatusRepository $teamStatusRepository,
        PlayerService $playerService,
        GameDayRepository $gameDayRepository,
        FakePlayerService $fakePlayerService
    ) {
        $this->entityManager        = $entityManager;
        $this->seasonService        = $seasonService;
        $this->teamStatusRepository = $teamStatusRepository;
        $this->playerService        = $playerService;
        $this->gameDayRepository    = $gameDayRepository;
        $this->fakePlayerService    = $fakePlayerService;
    }

    /**
     * @param int $id
     *
     * @return Team|null
     */
    public function findOneById(int $id): ?Team
    {
        return $this->entityManager->getRepository(Team::class)->find($id);
    }

    /**
     * @param Team $team
     */
    public function deleteTeam(Team $team): void
    {
        $players = $this->entityManager->getRepository(Player::class)
            ->findBy([
                'team' => $team,
                'server' => $team->getServer()
            ]
        );
        $coach = $team->getCoach();
        $gameDays = $this->gameDayRepository->getTeamGameDays($team);
        $countOfPlayersOnTheTeam = count($players);

        if ($countOfPlayersOnTheTeam !== 0) {
            $numberOfChunks = intval(ceil($countOfPlayersOnTheTeam / 1000));
            $chunks = array_chunk($players, $numberOfChunks);

            foreach($chunks as $chunk) {
                foreach($chunk as $player) {
                    $this->resetPlayerTeam($player);
                    $this->entityManager->persist($player);
                }

                $this->entityManager->flush();
            }

            foreach($players as $fakePlayer) {
                if($fakePlayer->getIsRealPlayer() === false) {
                    $this->entityManager->remove($fakePlayer);
                    $this->entityManager->flush();
                }
            }
        }

        if (count($gameDays) !== 0) {
            foreach($gameDays as $gameDay) {
                $gameDay->setTeamOne(null);
                $gameDay->setTeamTwo(null);

                $this->entityManager->remove($gameDay);
                $this->entityManager->flush();
            }
        }

        if($coach instanceof Coach) {
            $coach->setTeam(null);

            $this->entityManager->persist($coach);
            $this->entityManager->flush();
        }

        $this->entityManager->remove($team);
        $this->entityManager->flush();
    }

    /**
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getStandings()
    {
        /**
         * @var Server $server
         */
        $server = $this->entityManager->getRepository(Server::class)->findOneBy([
            'name' => Server::SERVER_ONE
        ]);

        $season = $this->seasonService->getActiveSeason($server);
        $teams = $this->teamStatusRepository->getStandingsList($season);

        return $teams;
    }

    /**
     * @param Player $player
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function buyoutPlayerFromTeam(Player $player)
    {
        $this->fakePlayerService->createFakePlayer($player->getTeam(), $player->getPosition()->getName());
        $this->resetPlayerTeam($player);

        $this->entityManager->persist($player);
        $this->entityManager->flush();
    }

    /**
     * @param Player $player
     * @param Team $team
     * @param float $salary
     * @param int $years
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function signPlayerToTeam(Player $player, Team $team, float $salary = 0, int $years = 2)
    {
        $this->checkAndFixFakePlayerLimitOnSigning($team, $player);

        $player->setTeam($team);
        $player->setContractYears($years);
        $player->setContractSalary($salary);
        $player->setSeasonEndsContract(
            $this->seasonService->getActiveSeason($player->getServer())->getId() + $years
        );

        $this->entityManager->persist($player);
        $this->entityManager->flush();

        $this->playerService->setPlayerJerseyNumber($team, $player);
    }

    /**
     * @param Team $team
     * @param Player $realPlayer
     */
    public function checkAndFixFakePlayerLimitOnSigning(Team $team, Player $realPlayer)
    {
        if(count($team->getPlayers()) > 9) {
            $fakePlayer = $team->getFakePlayerSamePosition($realPlayer->getPosition())->first();

            if (!$fakePlayer instanceof Player) {
                $fakePlayer = $team->getPlayers()->filter(function (Player $player) {
                    return $player->getIsRealPlayer() === false;
                })->first();
            }

            if ($fakePlayer instanceof Player) {
                $this->entityManager->remove($fakePlayer);
                $this->entityManager->flush();
            }
        }
    }

    /**
     * @param Player $player
     */
    public function resetPlayerTeam(Player $player)
    {
        $player->setTeam(null);
        $player->setContractSalary(null);
        $player->setContractYears(null);
        $player->setJerseyNumber(null);
        $player->setSeasonEndsContract(null);
    }

    /**
     * @param Server $server
     *
     * @return array
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLeagueLeaders(Server $server): array
    {
        $leaders = $this->seasonService->getSeasonLeadersArray($server);

        if(!$leaders) {
            return [];
        }

        return $leaders;
    }

    /**
     * @param Server $server
     *
     * @return int
     */
    public function getTeamsCount(Server $server): int
    {
        return count($server->getTeams());
    }
}
