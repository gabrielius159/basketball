<?php

namespace App\Service;

use App\Controller\PlayerController;
use App\Entity\Player;
use App\Entity\PlayerAttribute;
use App\Entity\PlayerAward;
use App\Entity\PlayerStats;
use App\Entity\Season;
use App\Entity\Team;
use App\Entity\TrainingCamp;
use App\Factory\Factory\PlayerDetailsFactory;
use App\Factory\Model\PlayerDetailsModel;
use App\Model\TeamDraft;
use App\Repository\PlayerRepository;
use App\Repository\PlayerStatsRepository;
use App\Repository\TeamRepository;
use App\Utils\Award;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class PlayerService
 *
 * @package App\Service
 */
class PlayerService
{
    const DRAFT_PICK_SALARY_A_GAME = 351;
    const ADD_TO_SALARY_FOR_A_RING = 25;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var SeasonService
     */
    private $seasonService;

    /**
     * @var ServerService
     */
    private $serverService;

    /**
     * @var PlayerStatsRepository
     */
    private $playerStatsRepository;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var PlayerDetailsFactory
     */
    private $playerDetailsFactory;

    /**
     * PlayerService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TeamRepository $teamRepository
     * @param SeasonService $seasonService
     * @param ServerService $serverService
     * @param PlayerStatsRepository $playerStatsRepository
     * @param PlayerRepository $playerRepository
     * @param PlayerDetailsFactory $playerDetailsFactory
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TeamRepository $teamRepository,
        SeasonService $seasonService,
        ServerService $serverService,
        PlayerStatsRepository $playerStatsRepository,
        PlayerRepository $playerRepository,
        PlayerDetailsFactory $playerDetailsFactory
    ) {
        $this->entityManager = $entityManager;
        $this->teamRepository = $teamRepository;
        $this->seasonService = $seasonService;
        $this->serverService = $serverService;
        $this->playerStatsRepository = $playerStatsRepository;
        $this->playerRepository = $playerRepository;
        $this->playerDetailsFactory = $playerDetailsFactory;
    }

    /**
     * @param Team $team
     * @param Player $player
     * @param bool $flush
     */
    public function setPlayerJerseyNumber(Team $team, Player $player, bool $flush = true)
    {
        /**
         * @var array $usedJerseys
         */
        $usedJerseys[] = $this->playerRepository->getTakenJerseyNumbers($team);
        $jerseyNumber = null;

        while($jerseyNumber == null) {
            $generatedNumber = rand(0, 99);
            if(!in_array($generatedNumber, $usedJerseys)) {
                $jerseyNumber = $generatedNumber;
            }
        }

        $player->setJerseyNumber($jerseyNumber);

        if($flush) {
            $this->entityManager->persist($player);
            $this->entityManager->flush();
        }
    }

    /**
     * @param Player $player
     *
     * @return array
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function draftPlayer(Player $player): array
    {
        $teamName = '';
        $drafted = null;
        $teamNumber = 1;

        /**
         * @var Team[] $teams
         */
        $teams = $this->teamRepository->findBy([
            'server' => $player->getServer()
        ]);

        if($teams) {
            $teamArray = [];

            foreach($teams as $teamCheck) {
                if(count($teamCheck->getRealPlayers()) < Team::DEFAULT_PLAYER_LIMIT_IN_TEAM) {
                    $teamArray[] = new TeamDraft($teamCheck);
                }
            }

            if(!empty($teamArray)) {
                shuffle($teamArray);

                $teamNumber = count($teamArray);
                $selectedTeamNumber = rand(0, $teamNumber - 1);

                /**
                 * @var TeamDraft $team
                 */
                $team = $teamArray[$selectedTeamNumber];

                self::checkAndFixFakePlayerLimitOnSigning($team->getTeam(), $player);

                $teamName = $team->getTeam()->getCity() . ' ' . $team->getTeam()->getName();
                $salary = self::DRAFT_PICK_SALARY_A_GAME;

                $player->setTeam($team->getTeam());
                $player->setContractYears(2);
                $player->setContractSalary($salary);
                $player->setSeasonEndsContract(
                    $this->seasonService->getActiveSeason($player->getServer())->getId() + 2
                );

                $this->entityManager->persist($player);
                $this->entityManager->flush();

                $drafted = $team;
            } else {
                $draftPick = 0;
            }
        } else {
            $draftPick = 0;
        }

        if($drafted) {
            $draftPick = rand(0, $teamNumber * 2) + 1;
            self::setPlayerJerseyNumber($player->getTeam(), $player);
        } else {
            $draftPick = 0;
        }

        return [
            [$teamName],
            [$draftPick]
        ];
    }

    /**
     * @param Player $player
     *
     * @return float
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function generateSalary(Player $player): float
    {
        $season = $this->seasonService->getActiveSeason($player->getServer());
        $playerStats = $player->getPlayerStats();
        $seasonsPlayed = count($playerStats) - 1;

        if($seasonsPlayed > 1) {
            $coef = 0.0;

            /**
             * @var Season $seasonBefore
             */
            $seasonBefore = $this->entityManager->getRepository(Season::class)->findOneBy([
                'id' => $season->getId() - 1
            ]);

            /**
             * @var PlayerStats $playerStatsSeasonBefore
             */
            $playerStatsSeasonBefore = $this->entityManager->getRepository(PlayerStats::class)->findOneBy([
                'season' => $seasonBefore,
                'player' => $player
            ]);

            $gamesPlayed = $playerStatsSeasonBefore->getGamesPlayed();

            if($playerStatsSeasonBefore->getPoints() > 0) {
                $coef += ($playerStatsSeasonBefore->getPoints() / $gamesPlayed) * PlayerStats::COEFFICIENT_POINTS;
            } elseif($playerStatsSeasonBefore->getRebounds() > 0) {
                $coef += ($playerStatsSeasonBefore->getRebounds() / $gamesPlayed) * PlayerStats::COEFFICIENT_REBOUNDS;
            } elseif($playerStatsSeasonBefore->getAssists() > 0) {
                $coef += ($playerStatsSeasonBefore->getAssists() / $gamesPlayed) * PlayerStats::COEFFICIENT_ASSISTS;
            } elseif($playerStatsSeasonBefore->getSteals() > 0) {
                $coef += ($playerStatsSeasonBefore->getSteals() / $gamesPlayed) * PlayerStats::COEFFICIENT_STEALS;
            } elseif($playerStatsSeasonBefore->getBlocks() > 0) {
                $coef += ($playerStatsSeasonBefore->getBlocks() / $gamesPlayed) * PlayerStats::COEFFICIENT_BLOCKS;
            }

            $championshipBonus = count($player->getPlayerAwards()->filter(function(PlayerAward $playerAward) {
                return $playerAward->getAward() === Award::PLAYER_CHAMPION;
            }));


            if($coef == 0.0) {
                return round(self::DRAFT_PICK_SALARY_A_GAME  + ($championshipBonus * self::ADD_TO_SALARY_FOR_A_RING), 2);
            }

            if($seasonsPlayed < 5) {
                $coef += $seasonsPlayed * PlayerStats::COEFFICIENT_SEASONS_PLAYED;
            }

            return round(((($coef / 10) * 1000) + $championshipBonus * self::ADD_TO_SALARY_FOR_A_RING), 2);
        }

        return round(self::DRAFT_PICK_SALARY_A_GAME, 2);
    }

    /**
     * @param Player $player
     * @param Team $team
     *
     * @return float
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function generateSalaryWithTeamBudget(Player $player, Team $team): float
    {
        $playerSalary = self::generateSalary($player);
        $teamBudget = $team->getBudget();
        $teamRealPlayerSalaries = 0.0;

        /**
         * @var Player $player
         */
        foreach($team->getPlayers() as $player) {
            if($player->getIsRealPlayer()) {
                $teamRealPlayerSalaries += $player->getContractSalary();
            }
        }

        if(($teamBudget - $teamRealPlayerSalaries) >= $playerSalary) {
            return round($playerSalary, 2);
        }

        return round(($teamBudget - $teamRealPlayerSalaries), 2);
    }

    /**
     * @param Team $team
     * @param Player $realPlayer
     */
    public function checkAndFixFakePlayerLimitOnSigning(Team $team, Player $realPlayer)
    {
        if(count($team->getPlayers()) > 9) {
            $fakePlayer = $team->getFakePlayerSamePosition($realPlayer->getPosition())->first();

            $this->entityManager->remove($fakePlayer);
            $this->entityManager->flush();
        }
    }

    /**
     * @param TrainingCamp $trainingCamp
     * @param Player $player
     *
     * @throws \Exception
     */
    public function improvePlayerAttribute(TrainingCamp $trainingCamp, Player $player)
    {
        $price = $trainingCamp->getPrice();

        $player->setMoney($player->getMoney() - $price);
        $player->setCamp($trainingCamp);

        $date = new \DateTime();
        $date->modify('+' . $trainingCamp->getDuration() . ' hours');

        $player->setTrainingFinishes($date);

        $this->entityManager->persist($player);
        $this->entityManager->flush();

        /**
         * @var PlayerAttribute $playerAttribute
         */
        $playerAttribute = $this->entityManager->getRepository(PlayerAttribute::class)->findOneBy([
            'player' => $player,
            'attribute' => $trainingCamp->getAttributeToImprove()
        ]);

        $playerAttribute->setValue(
            ($playerAttribute->getValue() + $trainingCamp->getSkillPoints()) >=99 ? 99 : $playerAttribute->getValue() + $trainingCamp->getSkillPoints()
        );

        $this->entityManager->persist($playerAttribute);
        $this->entityManager->flush();
    }

    /**
     * @param Player $player
     *
     * @return PlayerDetailsModel
     */
    public function getPlayerDetails(Player $player): PlayerDetailsModel
    {
        return $this->playerDetailsFactory->create($player);
    }
}
