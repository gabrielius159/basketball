<?php

namespace App\Service;

use App\Entity\Attribute;
use App\Entity\Country;
use App\Entity\GameDay;
use App\Entity\GameDayScores;
use App\Entity\GameType;
use App\Entity\Player;
use App\Entity\PlayerAttribute;
use App\Entity\PlayerAward;
use App\Entity\PlayerBadge;
use App\Entity\PlayerStats;
use App\Entity\Position;
use App\Entity\Season;
use App\Entity\Server;
use App\Entity\Team;
use App\Entity\TeamAward;
use App\Entity\TeamStatus;
use App\Event\CreatePlayerStatsEvent;
use App\Event\CreateTeamStatusEvent;
use App\Model\Game;
use App\Model\PlayerScore;
use App\Repository\GameDayRepository;
use App\Repository\PlayerRepository;
use App\Repository\PlayerStatsRepository;
use App\Repository\SeasonRepository;
use App\Repository\TeamStatusRepository;
use App\Utils\Award;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;


/**
 * Class SeasonService
 *
 * @package App\Service
 */
class SeasonService
{
    /**
     * @var SeasonRepository
     */
    private $seasonRepository;

    /**
     * @var PlayerStatsRepository
     */
    private $playerStatsRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var GameDayRepository
     */
    private $gameDayRepository;

    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var AttributeService
     */
    private $attributeService;

    /**
     * @var TeamStatusRepository
     */
    private $teamStatusRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * SeasonService constructor.
     *
     * @param SeasonRepository         $seasonRepository
     * @param PlayerStatsRepository    $playerStatsRepository
     * @param EntityManagerInterface   $entityManager
     * @param GameDayRepository        $gameDayRepository
     * @param PlayerRepository         $playerRepository
     * @param AttributeService         $attributeService
     * @param TeamStatusRepository     $teamStatusRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        SeasonRepository $seasonRepository,
        PlayerStatsRepository $playerStatsRepository,
        EntityManagerInterface $entityManager,
        GameDayRepository $gameDayRepository,
        PlayerRepository $playerRepository,
        AttributeService $attributeService,
        TeamStatusRepository $teamStatusRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->seasonRepository = $seasonRepository;
        $this->playerStatsRepository = $playerStatsRepository;
        $this->entityManager = $entityManager;
        $this->gameDayRepository = $gameDayRepository;
        $this->playerRepository = $playerRepository;
        $this->attributeService = $attributeService;
        $this->teamStatusRepository = $teamStatusRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Server $server
     *
     * @return Season
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getActiveSeason(Server $server): Season
    {
        $season = $this->seasonRepository->getActiveSeason($server);

        if(!$season instanceof Season) {
            $season = self::createAndReturnNewSeason($server);
        }

        return $season;
    }

    /**
     * @param Server $server
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createNewSeasonWithoutReturn(Server $server)
    {
        $activeSeason = $this->seasonRepository->getActiveSeason($server);

        if(!$activeSeason instanceof Season) {
            $newSeason = (new Season())
                ->setStatus(Season::STATUS_PREPARING)
                ->setServer($server)
            ;

            $this->entityManager->persist($newSeason);
            $this->entityManager->flush();

            self::createNewEntities($newSeason);
        }
    }

    /**
     * @param Server $server
     *
     * @return bool
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function checkIfActiveSeasonExists(Server $server): bool
    {
        $season = $this->seasonRepository->getActiveSeason($server);

        return $season instanceof Season;
    }

    /**
     * @todo refactor this function and return array with image
     * @param Server $server
     *
     * @return array
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getSeasonLeadersArray(Server $server): array
    {
        $season = self::getActiveSeason($server);

        if($season) {
            return [
                'points' => $this->playerStatsRepository->getLeadersByCategory($season, PlayerStats::CATEGORY_POINTS),
                'rebounds' => $this->playerStatsRepository->getLeadersByCategory($season, PlayerStats::CATEGORY_REBOUNDS),
                'assists' => $this->playerStatsRepository->getLeadersByCategory($season, PlayerStats::CATEGORY_ASSISTS),
                'steals' => $this->playerStatsRepository->getLeadersByCategory($season, PlayerStats::CATEGORY_STEALS),
                'blocks' => $this->playerStatsRepository->getLeadersByCategory($season, PlayerStats::CATEGORY_BLOCKS),
            ];
        }

        return [];
    }

    /**
     * @param Season $season
     *
     * @return null|Team
     */
    public function getLastSeasonChampions(Season $season): ?Team
    {
        $lastSeason = $this->entityManager->getRepository(Season::class)->find($season->getId() -1);

        if (!$lastSeason instanceof Season) {
            return null;
        }

        $teamAward = $lastSeason->getTeamAward();

        if (!$teamAward instanceof TeamAward) {
            return null;
        }

        $team = $teamAward->getTeam();

        if (!$team instanceof Team) {
            return null;
        }

        return $team;
    }

    /**
     * @param Season $season
     *
     * @return null|Player
     */
    public function getLastSeasonMVP(Season $season): ?Player
    {
        $lastSeason = $this->entityManager->getRepository(Season::class)->find($season->getId() -1);

        if (!$lastSeason instanceof Season) {
            return null;
        }

        $mvp = $this->entityManager->getRepository(PlayerAward::class)->findOneBy([
            'season' => $lastSeason,
            'award' => Award::PLAYER_MVP
        ]);

        if (!$mvp instanceof PlayerAward) {
            return null;
        }

        $player = $mvp->getPlayer();

        if (!$player instanceof Player) {
            return null;
        }

        return $player;
    }

    /**
     * @param Season $season
     *
     * @return null|Player
     */
    public function getLastSeasonDPOY(Season $season): ?Player
    {
        $lastSeason = $this->entityManager->getRepository(Season::class)->find($season->getId() -1);

        if (!$lastSeason instanceof Season) {
            return null;
        }

        $dpoy = $this->entityManager->getRepository(PlayerAward::class)->findOneBy([
            'season' => $lastSeason,
            'award' => Award::PLAYER_DPOY
        ]);

        if (!$dpoy instanceof PlayerAward) {
            return null;
        }

        $player = $dpoy->getPlayer();

        if (!$player instanceof Player) {
            return null;
        }

        return $player;
    }

    /**
     * @param Season $season
     *
     * @return null|Player
     */
    public function getLastSeasonROTY(Season $season): ?Player
    {
        $lastSeason = $this->entityManager->getRepository(Season::class)->find($season->getId() -1);

        if (!$lastSeason instanceof Season) {
            return null;
        }

        $roty = $this->entityManager->getRepository(PlayerAward::class)->findOneBy([
            'season' => $lastSeason,
            'award' => Award::PLAYER_ROTY
        ]);

        if (!$roty instanceof PlayerAward) {
            return null;
        }

        $player = $roty->getPlayer();

        if (!$player instanceof Player) {
            return null;
        }

        return $player;
    }

    /**
     * @param Server $server
     * @param int $seasonId
     */
    public function checkContracts(Server $server, int $seasonId)
    {
        $players = $this->playerRepository->getRealPlayersWithExpiringContract($server, $seasonId);

        if($playerNumber = count($players) > 0) {
            $numberOfChunks = intval(ceil($playerNumber / 1000));
            $chunks = array_chunk($players, $numberOfChunks);

            foreach($chunks as $chunk) {
                /**
                 * @var Player $player
                 */
                foreach($chunk as $player) {
                    $player->setTeam(null);
                    $player->setContractSalary(null);
                    $player->setContractYears(null);
                    $player->setJerseyNumber(null);
                    $player->setSeasonEndsContract(null);
                }

                $this->entityManager->flush();
            }
        }
    }

    /**
     * @param Server $server
     *
     * @return bool
     */
    public function teamsHaveCoach(Server $server): bool
    {
        $teams = $this->entityManager->getRepository(Team::class)->findBy([
            'server' => $server
        ]);

        /**
         * @var Team $team
         */
        foreach($teams as $team) {
            if(!$team->getCoach()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $status
     *
     * @return string
     */
    public function getSeasonStatusName(string $status): string
    {
        switch($status) {
            case $status == Season::STATUS_ACTIVE: {
                return 'In progress';
            }
            case $status == Season::STATUS_FINISHED: {
                return 'Finished';
            }
            case $status == Season::STATUS_PREPARING: {
                return 'Preseason';
            }
        }

        return 'Unknown status';
    }

    /**
     * @param Server $server
     * @param Season $season
     *
     * @throws \Exception
     */
    public function generateSchedule(Server $server, Season $season)
    {
        $teams = $this->entityManager->getRepository(Team::class)->createQueryBuilder('t')
            ->where('t.server = :server')
            ->setParameter('server', $server)
            ->getQuery()
            ->getResult()
        ;

        if($teams) {
            $matchDay = new \DateTime();
            $matchDay->modify('+1 day');

            $games = [];

            foreach($teams as $teamOne) {
                foreach($teams as $teamTwo) {
                    if($teamOne !== $teamTwo) {
                        $games[] = new Game($teamOne, $teamTwo);
                    }
                }
            }

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
    }

    /**
     * @param int $teamId
     * @param string $type
     *
     * @return float
     */
    public function getRating(int $teamId, string $type = 'ASSISTS'): float
    {
        $team = $this->entityManager->getRepository(Team::class)->find($teamId);

        if($team) {
            switch($type) {
                case 'ASSISTS': {
                    $value = 0;
                    /**
                     * @var Player $player
                     */
                    foreach($team->getPlayers() as $player) {
                        $playerAttributes = $player->getPlayerAttributes()->filter(function(PlayerAttribute $playerAttribute) {
                            return $playerAttribute->getAttribute()->getGameType()->getName() == GameType::TYPE_ASSISTING;
                        });

                        foreach($playerAttributes as $pA) {
                            $value += $pA->getValue();
                        }
                    }

                    return round(($value / 2), 2);
                }
                case 'REBOUNDS': {
                    $value = 0;
                    /**
                     * @var Player $player
                     */
                    foreach($team->getPlayers() as $player) {
                        $playerAttributes = $player->getPlayerAttributes()->filter(function(PlayerAttribute $playerAttribute) {
                            return $playerAttribute->getAttribute()->getGameType()->getName() == GameType::TYPE_REBOUNDING;
                        });

                        foreach($playerAttributes as $pA) {
                            $value += $pA->getValue();
                        }
                    }

                    return round(($value / 2), 2);
                }
                case 'STEALS': {
                    $value = 0;
                    /**
                     * @var Player $player
                     */
                    foreach($team->getPlayers() as $player) {
                        $playerAttributes = $player->getPlayerAttributes()->filter(function(PlayerAttribute $playerAttribute) {
                            return $playerAttribute->getAttribute()->getGameType()->getName() == GameType::TYPE_STEALING;
                        });

                        foreach($playerAttributes as $pA) {
                            $value += $pA->getValue();
                        }
                    }

                    return round(($value / 2), 2);
                }
                case 'BLOCKS': {
                    $value = 0;
                    /**
                     * @var Player $player
                     */
                    foreach($team->getPlayers() as $player) {
                        $playerAttributes = $player->getPlayerAttributes()->filter(function(PlayerAttribute $playerAttribute) {
                            return $playerAttribute->getAttribute()->getGameType()->getName() == GameType::TYPE_BLOCKING;
                        });

                        $temp = 0;

                        foreach($playerAttributes as $pA) {
                            $temp += $pA->getValue();
                        }

                        $value += $temp / 2;
                    }

                    return round(($value / 10), 2);
                }
            }
        }

        return 0;
    }

    /**
     * @param GameDay $gameDay
     */
    public function playGame(GameDay $gameDay)
    {
        $this->faker = Factory::create();
        $teamOne = $gameDay->getTeamOne();
        $teamTwo = $gameDay->getTeamTwo();

        $teamOneStatus = $teamOne->getCurrentTeamStatus();
        $teamTwoStatus = $teamTwo->getCurrentTeamStatus();

        $teamOnePlayerScores = [];
        $teamTwoPlayerScores = [];

        $teamOneScore = 0;
        $teamTwoScore = 0;

        $teamOneCoach = $teamOne->getCoach();
        $teamTwoCoach = $teamTwo->getCoach();

        $teamOneCoachType = $teamOneCoach->getFirstGameType()->getName();
        $teamTwoCoachType = $teamTwoCoach->getFirstGameType()->getName();


        $teamOneBonus = (($this->getRating($teamOne->getId(), 'ASSISTS') * $this->faker->randomFloat(2, 0.01, 0.05))
                + ($this->getRating($teamOne->getId(), 'REBOUNDS') * $this->faker->randomFloat(2, 0.01, 0.05)))
            - (($this->getRating($teamTwo->getId(), 'STEALS') * $this->faker->randomFloat(2, 0.04, 0.06))
                + ($this->getRating($teamTwo->getId(), 'BLOCKS') * $this->faker->randomFloat(2, 0.04, 0.06)));

        $teamTwoBonus = (($this->getRating($teamTwo->getId(), 'ASSISTS') * $this->faker->randomFloat(2, 0.01, 0.05))
                + ($this->getRating($teamTwo->getId(), 'REBOUNDS') * $this->faker->randomFloat(2, 0.01, 0.05)))
            - (($this->getRating($teamOne->getId(), 'STEALS') * $this->faker->randomFloat(2, 0.04, 0.06))
                + ($this->getRating($teamOne->getId(), 'BLOCKS') * $this->faker->randomFloat(2, 0.04, 0.06)));

        foreach($teamOne->getPlayers() as $player) {
            /**
             * We create PlayerScore model for storing data
             */
            $playerScore = new PlayerScore($player);

            /**
             * Player attributes
             */
            $playerAttributes = $player->getPlayerAttributes();

            /**
             * ---------------------------------------------------------------------------------------------------------
             *
             * Generating scoring
             *
             * ---------------------------------------------------------------------------------------------------------
             */
            $scoringBadges = count($player->getPlayerBadges()->filter(function (PlayerBadge $playerBadge) {
                return $playerBadge->getBadge()->getAttribute()->getGameType() === GameType::TYPE_SCORING;
            }));

            $scoreAttributes = $playerAttributes->filter(function (PlayerAttribute $playerAttribute) {
                return $playerAttribute->getAttribute()->getGameType()->getType() === GameType::TYPE_SCORING;
            });

            $scoreValue = 0;
            foreach($scoreAttributes as $scoreAttribute) {
                $scoreValue += $scoreAttribute->getValue();
            }

            $scoringRange = [
                'fake' => [
                    'min' => 0,
                    'max' => 0.4
                ],
                'real' => [
                    'min' => 0.1,
                    'max' => 0.4
                ]
            ];

            if($teamOneCoachType == GameType::TYPE_SCORING) {
                $scoringRange['real']['max'] += 0.1;
            }

            if($player->getIsRealPlayer() === false) {
                $points = floor((($scoreValue / 2) * $this->faker->randomFloat(2, $scoringRange['fake']['min'], $scoringRange['fake']['max'])) + $teamOneBonus);
            } else {
                $points = floor((($scoreValue / 2) * $this->faker->randomFloat(2, $scoringRange['real']['min'], $scoringRange['real']['max'])) + $teamOneBonus);
                if($scoringBadges > 0) {
                    $points += ceil(($scoringBadges * 2) * $this->faker->randomFloat(2, 0.5, 1));
                }
            }

            $playerScore->setPoints($points < 0 ? 0 : $points);

            /**
             * ---------------------------------------------------------------------------------------------------------
             *
             * Generating assists
             *
             * ---------------------------------------------------------------------------------------------------------
             */
            $assistAttributes = $playerAttributes->filter(function (PlayerAttribute $playerAttribute) {
                return $playerAttribute->getAttribute()->getGameType()->getType() === GameType::TYPE_ASSISTING;
            });

            $assistBadges = count($player->getPlayerBadges()->filter(function (PlayerBadge $playerBadge) {
                return $playerBadge->getBadge()->getAttribute()->getGameType() === GameType::TYPE_ASSISTING;
            }));

            $assistValue = 0;
            foreach($assistAttributes as $assistAttribute) {
                $assistValue += $assistAttribute->getValue();
            }

            $assistRange = [
                'fake' => [
                    'min' => 0,
                    'max' => 0.14
                ],
                'real' => [
                    'min' => 0.05,
                    'max' => 0.14
                ]
            ];

            if($teamOneCoachType == GameType::TYPE_ASSISTING) {
                $assistRange['real']['max'] += 0.05;
            }

            if($player->getIsRealPlayer() === false) {
                $assists = floor(($assistValue / 2) * $this->faker->randomFloat(2, $assistRange['fake']['min'], $assistRange['fake']['max']));
            } else {
                $assists = floor(($assistValue / 2) * $this->faker->randomFloat(2, $assistRange['real']['min'], $assistRange['real']['max']));
                if($assistBadges > 0) {
                    $assists += ceil(($assistBadges * 2) * $this->faker->randomFloat(2, 0.5, 1));
                }
            }

            $playerScore->setAssists($assists);

            /**
             * ---------------------------------------------------------------------------------------------------------
             *
             * Generating rebounding
             *
             * ---------------------------------------------------------------------------------------------------------
             */
            $reboundingAttributes = $playerAttributes->filter(function (PlayerAttribute $playerAttribute) {
                return $playerAttribute->getAttribute()->getGameType()->getType() === GameType::TYPE_REBOUNDING;
            });

            $reboundingBadges = count($player->getPlayerBadges()->filter(function (PlayerBadge $playerBadge) {
                return $playerBadge->getBadge()->getAttribute()->getGameType() === GameType::TYPE_REBOUNDING;
            }));

            $reboundValue = 0;
            foreach($reboundingAttributes as $reboundingAttribute) {
                $reboundValue += $reboundingAttribute->getValue();
            }

            $reboundRange = [
                'fake' => [
                    'min' => 0,
                    'max' => 0.14
                ],
                'real' => [
                    'min' => 0.05,
                    'max' => 0.14
                ]
            ];

            if($teamOneCoachType == GameType::TYPE_REBOUNDING) {
                $reboundRange['real']['max'] += 0.05;
            }

            if($player->getIsRealPlayer() === false) {
                $rebounds = floor(($reboundValue / 2) * $this->faker->randomFloat(2, $reboundRange['fake']['min'], $reboundRange['fake']['max']));
            } else {
                $rebounds = floor(($reboundValue / 2) * $this->faker->randomFloat(2, $reboundRange['real']['min'], $reboundRange['real']['max']));
                if($assistBadges > 0) {
                    $rebounds += ceil(($reboundingBadges * 2) * $this->faker->randomFloat(2, 0.5, 1));
                }
            }

            $playerScore->setRebounds($rebounds);

            /**
             * ---------------------------------------------------------------------------------------------------------
             *
             * Generating steals
             *
             * ---------------------------------------------------------------------------------------------------------
             */
            $stealAttributes = $playerAttributes->filter(function (PlayerAttribute $playerAttribute) {
                return $playerAttribute->getAttribute()->getGameType()->getType() === GameType::TYPE_STEALING;
            });

            $stealBadges = count($player->getPlayerBadges()->filter(function (PlayerBadge $playerBadge) {
                return $playerBadge->getBadge()->getAttribute()->getGameType() === GameType::TYPE_STEALING;
            }));

            $stealValue = 0;
            foreach($stealAttributes as $stealAttribute) {
                $stealValue += $stealAttribute->getValue();
            }

            $stealRange = [
                'all' => [
                    'min' => 0,
                    'max' => 0.05
                ]
            ];

            if($teamOneCoachType == GameType::TYPE_STEALING) {
                $stealRange['all']['max'] += 0.04;
            }

            $steals = floor(($stealValue / 2) * $this->faker->randomFloat(2, $stealRange['all']['min'], $stealRange['all']['max']));

            if($stealBadges > 0) {
                $steals += ceil(($stealBadges * 2) * $this->faker->randomFloat(2, 0.5, 1));
            }

            $playerScore->setSteals($steals);

            /**
             * ---------------------------------------------------------------------------------------------------------
             *
             * Generating blocks
             *
             * ---------------------------------------------------------------------------------------------------------
             */
            $blockAttributes = $playerAttributes->filter(function (PlayerAttribute $playerAttribute) {
                return $playerAttribute->getAttribute()->getGameType()->getType() === GameType::TYPE_BLOCKING;
            });

            $blockBadges = count($player->getPlayerBadges()->filter(function (PlayerBadge $playerBadge) {
                return $playerBadge->getBadge()->getAttribute()->getGameType() === GameType::TYPE_BLOCKING;
            }));

            $blockValue = 0;
            foreach($blockAttributes as $blockAttribute) {
                $blockValue += $blockAttribute->getValue();
            }

            $blockRange = [
                'all' => [
                    'min' => 0,
                    'max' => 0.05
                ]
            ];

            if($teamOneCoachType == GameType::TYPE_BLOCKING) {
                $blockRange['all']['max'] += 0.04;
            }

            $blocks = floor(($blockValue / 2) * $this->faker->randomFloat(2, $blockRange['all']['min'], $blockRange['all']['max']));

            if($blockBadges > 0) {
                $blocks += ceil(($blockBadges * 2) * $this->faker->randomFloat(2, 0.5, 1));
            }

            $playerScore->setBlocks($blocks);


            /**
             * ---------------------------------------------------------------------------------------------------------
             *
             * Adding stats to player
             *
             * ---------------------------------------------------------------------------------------------------------
             */
            $playerStats = $player->getCurrentPlayerStats();

            $playerStats->setGamesPlayed($playerStats->getGamesPlayed() + 1);
            $playerStats->setPoints($playerStats->getPoints() + $playerScore->getPoints());
            $playerStats->setRebounds($playerStats->getRebounds() + $playerScore->getRebounds());
            $playerStats->setAssists($playerStats->getAssists() + $playerScore->getAssists());
            $playerStats->setSteals($playerStats->getSteals() + $playerScore->getSteals());
            $playerStats->setBlocks($playerStats->getBlocks() + $playerScore->getBlocks());

            $this->entityManager->persist($playerStats);
            $this->entityManager->flush();

            $teamOnePlayerScores[] = $playerScore;
            $teamOneScore += $playerScore->getPoints();

            $player->setMoney($player->getMoney() + $player->getContractSalary());

            $this->entityManager->persist($player);
            $this->entityManager->flush();
        }


        foreach($teamTwo->getPlayers() as $player) {
            $playerScore = new PlayerScore($player);

            $playerAttributes = $player->getPlayerAttributes();

            /**
             * ---------------------------------------------------------------------------------------------------------
             *
             * Generating scoring
             *
             * ---------------------------------------------------------------------------------------------------------
             */
            $scoringBadges = count($player->getPlayerBadges()->filter(function (PlayerBadge $playerBadge) {
                return $playerBadge->getBadge()->getAttribute()->getGameType() === GameType::TYPE_SCORING;
            }));

            $scoreAttributes = $playerAttributes->filter(function (PlayerAttribute $playerAttribute) {
                return $playerAttribute->getAttribute()->getGameType()->getType() === GameType::TYPE_SCORING;
            });

            $scoreValue = 0;
            foreach($scoreAttributes as $scoreAttribute) {
                $scoreValue += $scoreAttribute->getValue();
            }

            $scoringRange = [
                'fake' => [
                    'min' => 0,
                    'max' => 0.4
                ],
                'real' => [
                    'min' => 0.1,
                    'max' => 0.4
                ]
            ];

            if($teamTwoCoachType == GameType::TYPE_SCORING) {
                $scoringRange['real']['max'] += 0.1;
            }

            if($player->getIsRealPlayer() === false) {
                $points = floor((($scoreValue / 2) * $this->faker->randomFloat(2, $scoringRange['fake']['min'], $scoringRange['fake']['max'])) + $teamTwoBonus);
            } else {
                $points = floor((($scoreValue / 2) * $this->faker->randomFloat(2, $scoringRange['real']['min'], $scoringRange['real']['max'])) + $teamTwoBonus);
                if($scoringBadges > 0) {
                    $points += ceil(($scoringBadges * 2) * $this->faker->randomFloat(2, 0.5, 1));
                }
            }


            $playerScore->setPoints($points < 0 ? 0 : $points);

            /**
             * ---------------------------------------------------------------------------------------------------------
             *
             * Generating assists
             *
             * ---------------------------------------------------------------------------------------------------------
             */
            $assistBadges = count($player->getPlayerBadges()->filter(function (PlayerBadge $playerBadge) {
                return $playerBadge->getBadge()->getAttribute()->getGameType() === GameType::TYPE_ASSISTING;
            }));

            $assistAttributes = $playerAttributes->filter(function (PlayerAttribute $playerAttribute) {
                return $playerAttribute->getAttribute()->getGameType()->getType() === GameType::TYPE_ASSISTING;
            });

            $assistValue = 0;
            foreach($assistAttributes as $assistAttribute) {
                $assistValue += $assistAttribute->getValue();
            }

            $assistRange = [
                'fake' => [
                    'min' => 0,
                    'max' => 0.14
                ],
                'real' => [
                    'min' => 0.05,
                    'max' => 0.14
                ]
            ];

            if($teamTwoCoachType == GameType::TYPE_ASSISTING) {
                $assistRange['real']['max'] += 0.05;
            }

            if($player->getIsRealPlayer() === false) {
                $assists = floor(($assistValue / 2) * $this->faker->randomFloat(2, $assistRange['fake']['min'], $assistRange['fake']['max']));
            } else {
                $assists = floor(($assistValue / 2) * $this->faker->randomFloat(2, $assistRange['real']['min'], $assistRange['real']['max']));
                if($assistBadges > 0) {
                    $assists += ceil(($assistBadges * 2) * $this->faker->randomFloat(2, 0.5, 1));
                }
            }

            $playerScore->setAssists($assists);

            /**
             * ---------------------------------------------------------------------------------------------------------
             *
             * Generating rebounds
             *
             * ---------------------------------------------------------------------------------------------------------
             */
            $reboundingBadges = count($player->getPlayerBadges()->filter(function (PlayerBadge $playerBadge) {
                return $playerBadge->getBadge()->getAttribute()->getGameType() === GameType::TYPE_REBOUNDING;
            }));

            $reboundingAttributes = $playerAttributes->filter(function (PlayerAttribute $playerAttribute) {
                return $playerAttribute->getAttribute()->getGameType()->getType() === GameType::TYPE_REBOUNDING;
            });

            $reboundValue = 0;
            foreach($reboundingAttributes as $reboundingAttribute) {
                $reboundValue += $reboundingAttribute->getValue();
            }

            $reboundRange = [
                'fake' => [
                    'min' => 0,
                    'max' => 0.14
                ],
                'real' => [
                    'min' => 0.05,
                    'max' => 0.14
                ]
            ];

            if($teamTwoCoachType == GameType::TYPE_REBOUNDING) {
                $reboundRange['real']['max'] += 0.05;
            }

            if($player->getIsRealPlayer() === false) {
                $rebounds = floor(($reboundValue / 2) * $this->faker->randomFloat(2, $reboundRange['fake']['min'], $reboundRange['fake']['max']));
            } else {
                $rebounds = floor(($reboundValue / 2) * $this->faker->randomFloat(2, $reboundRange['real']['min'], $reboundRange['real']['max']));
                if($assistBadges > 0) {
                    $rebounds += ceil(($reboundingBadges * 2) * $this->faker->randomFloat(2, 0.5, 1));
                }
            }


            $playerScore->setRebounds($rebounds);

            /**
             * ---------------------------------------------------------------------------------------------------------
             *
             * Generating steals
             *
             * ---------------------------------------------------------------------------------------------------------
             */
            $stealBadges = count($player->getPlayerBadges()->filter(function (PlayerBadge $playerBadge) {
                return $playerBadge->getBadge()->getAttribute()->getGameType() === GameType::TYPE_STEALING;
            }));

            $stealAttributes = $playerAttributes->filter(function (PlayerAttribute $playerAttribute) {
                return $playerAttribute->getAttribute()->getGameType()->getType() === GameType::TYPE_STEALING;
            });

            $stealValue = 0;
            foreach($stealAttributes as $stealAttribute) {
                $stealValue += $stealAttribute->getValue();
            }

            $stealRange = [
                'all' => [
                    'min' => 0,
                    'max' => 0.05
                ]
            ];

            if($teamTwoCoachType == GameType::TYPE_STEALING) {
                $stealRange['all']['max'] += 0.04;
            }

            $steals = floor(($stealValue / 2) * $this->faker->randomFloat(2, $stealRange['all']['min'], $stealRange['all']['max']));
            if($stealBadges > 0) {
                $steals += ceil(($stealBadges * 2) * $this->faker->randomFloat(2, 0.5, 1));
            }
            $playerScore->setSteals($steals);

            /**
             * ---------------------------------------------------------------------------------------------------------
             *
             * Generating block
             *
             * ---------------------------------------------------------------------------------------------------------
             */
            $blockBadges = count($player->getPlayerBadges()->filter(function (PlayerBadge $playerBadge) {
                return $playerBadge->getBadge()->getAttribute()->getGameType() === GameType::TYPE_BLOCKING;
            }));

            $blockAttributes = $playerAttributes->filter(function (PlayerAttribute $playerAttribute) {
                return $playerAttribute->getAttribute()->getGameType()->getType() === GameType::TYPE_BLOCKING;
            });

            $blockValue = 0;
            foreach($blockAttributes as $blockAttribute) {
                $blockValue += $blockAttribute->getValue();
            }

            $blockRange = [
                'all' => [
                    'min' => 0,
                    'max' => 0.05
                ]
            ];

            if($teamTwoCoachType == GameType::TYPE_BLOCKING) {
                $blockRange['all']['max'] += 0.04;
            }

            $blocks = floor(($blockValue / 2) * $this->faker->randomFloat(2, $blockRange['all']['min'], $blockRange['all']['max']));
            if($blockBadges > 0) {
                $blocks += ceil(($blockBadges * 2) * $this->faker->randomFloat(2, 0.5, 1));
            }
            $playerScore->setBlocks($blocks);


            $playerStats = $player->getCurrentPlayerStats();

            $playerStats->setGamesPlayed($playerStats->getGamesPlayed() + 1);
            $playerStats->setPoints($playerStats->getPoints() + $playerScore->getPoints());
            $playerStats->setRebounds($playerStats->getRebounds() + $playerScore->getRebounds());
            $playerStats->setAssists($playerStats->getAssists() + $playerScore->getAssists());
            $playerStats->setSteals($playerStats->getSteals() + $playerScore->getSteals());
            $playerStats->setBlocks($playerStats->getBlocks() + $playerScore->getBlocks());

            $this->entityManager->persist($playerStats);
            $this->entityManager->flush();

            $teamTwoPlayerScores[] = $playerScore;
            $teamTwoScore += $playerScore->getPoints();

            $player->setMoney($player->getMoney() + $player->getContractSalary());

            $this->entityManager->persist($player);
            $this->entityManager->flush();
        }

        $gameDayScore = (new GameDayScores())
            ->setGameDay($gameDay)
            ->setTeamOnePlayerStats($this->playerStatsToArray($teamOnePlayerScores))
            ->setTeamTwoPlayerStats($this->playerStatsToArray($teamTwoPlayerScores))
            ->setTeamOneScore($teamOneScore)
            ->setTeamTwoScore($teamTwoScore)
        ;

        $gameDay->setGameDayScores($gameDayScore);
        $gameDay->setStatus(GameDay::STATUS_FINISHED);

        $this->entityManager->persist($gameDay);
        $this->entityManager->flush();

        if($teamOneScore > $teamTwoScore) {
            $teamOneStatus->setWin($teamOneStatus->getWin() + 1);
            $teamTwoStatus->setLose($teamTwoStatus->getLose() + 1);
        } else {
            $teamTwoStatus->setWin($teamTwoStatus->getWin() + 1);
            $teamOneStatus->setLose($teamOneStatus->getLose() + 1);
        }

        $teamOneStatus->setPoints($teamOneStatus->getPoints() + $teamOneScore);
        $teamTwoStatus->setPoints($teamTwoStatus->getPoints() + $teamTwoScore);

        $this->entityManager->persist($teamOneStatus);
        $this->entityManager->persist($teamTwoStatus);
        $this->entityManager->flush();
    }

    /**
     * @param array $playerScores
     *
     * @return array
     */
    public function playerStatsToArray(array $playerScores): array
    {
        $data = [];

        /**
         * @var PlayerScore $playerScore
         */
        foreach($playerScores as $playerScore) {
            $player = [
                'points' => $playerScore->getPoints(),
                'rebounds' => $playerScore->getRebounds(),
                'assists' => $playerScore->getAssists(),
                'steals' => $playerScore->getSteals(),
                'blocks' => $playerScore->getBlocks(),
                'player' => $playerScore->getPlayer()->getFirstname() . ' ' . $playerScore->getPlayer()->getLastname()
            ];

            $data[] = $player;
        }

        return $data;
    }

    /**
     * @param Server $server
     *
     * @return GameDay|null
     *
     * @throws \Exception
     */
    public function getTodayGame(Server $server): ?GameDay
    {
        $season = $this->getActiveSeason($server);

        $today = $this->gameDayRepository->getByDate(new \DateTime(), $season);

        return $today;
    }

    /**
     * @param Server $server
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getTwoUpcomingGames(Server $server)
    {
        $season = $this->getActiveSeason($server);

        $games = $this->gameDayRepository->getTwoUpcomingGames($season);

        if(!$games) {
            return [];
        }

        return $games;
    }

    /**
     * @param Server $server
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function generateFakePlayers(Server $server)
    {
        $teams = $this->entityManager->getRepository(Team::class)->findBy([
            'server' => $server
        ]);

        /**
         * @var Team $team
         */
        foreach($teams as $team) {
            $teamPlayers = $team->getPlayers();

            $pg = $teamPlayers->filter(function (Player $player) {
                return $player->getPosition()->getName() === Position::POINT_GUARD;
            });

            $sg = $teamPlayers->filter(function (Player $player) {
                return $player->getPosition()->getName() === Position::SHOOTING_GUARD;
            });

            $sf = $teamPlayers->filter(function (Player $player) {
                return $player->getPosition()->getName() === Position::SMALL_FORWARD;
            });

            $pf = $teamPlayers->filter(function (Player $player) {
                return $player->getPosition()->getName() === Position::POWER_FORWARD;
            });

            $c = $teamPlayers->filter(function (Player $player) {
                return $player->getPosition()->getName() === Position::CENTER;
            });

            if($pg) {
                self::createFakePlayer($team, Position::POINT_GUARD, (2 - count($pg)));
            }

            if($sg) {
                self::createFakePlayer($team, Position::SHOOTING_GUARD, (2 - count($sg)));
            }

            if($sf) {

                self::createFakePlayer($team, Position::SMALL_FORWARD, (2 - count($sf)));
            }

            if($pf) {
                self::createFakePlayer($team, Position::POWER_FORWARD, (2 - count($pf)));
            }

            if($c) {
                self::createFakePlayer($team, Position::CENTER, (2 - count($c)));
            }
        }
    }

    /**
     * @param Team $team
     * @param string $position
     * @param int $iterations
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createFakePlayer(Team $team, string $position, int $iterations = 1)
    {
        $this->faker = Factory::create();

        $minHeight = 150;
        $maxHeight = 220;
        $minWeight = 60;
        $maxWeight = 150;

        switch ($position) {
            case Position::POINT_GUARD: {
                $minHeight = 170;
                $maxHeight = 190;
                $minWeight = 60;
                $maxWeight = 80;

                break;
            }
            case Position::SHOOTING_GUARD: {
                $minHeight = 188;
                $maxHeight = 201;
                $minWeight = 60;
                $maxWeight = 80;

                break;
            }
            case Position::SMALL_FORWARD: {
                $minHeight = 198;
                $maxHeight = 206;
                $minWeight = 80;
                $maxWeight = 100;

                break;
            }
            case Position::POWER_FORWARD: {
                $minHeight = 201;
                $maxHeight = 213;
                $minWeight = 80;
                $maxWeight = 110;

                break;
            }
            case Position::CENTER: {
                $minHeight = 203;
                $maxHeight = 216;
                $minWeight = 90;
                $maxWeight = 120;

                break;
            }
        }

        for($i = 0; $i < $iterations; $i++) {
            /**
             * @var Country $country
             */
            $country = $this->entityManager
                ->getRepository(Country::class)
                ->find(rand(1, 249));

            /**
             * @var Position $playerPosition
             */
            $playerPosition = $this->entityManager
                ->getRepository(Position::class)
                ->findOneBy([
                    'name' => $position
                ]);

            $validGameTypes = [
                GameType::TYPE_SCORING,
                GameType::TYPE_ASSISTING,
                GameType::TYPE_REBOUNDING,
                GameType::TYPE_STEALING,
                GameType::TYPE_BLOCKING
            ];

            /**
             * @var GameType $firstGameType
             */
            $firstGameType = $this->entityManager
                ->getRepository(GameType::class)
                ->findOneBy(['type' => $validGameTypes[rand(0, 4)]]);

            /**
             * @var GameType $secondGameType
             */
            $secondGameType = $this->entityManager
                ->getRepository(GameType::class)
                ->findOneBy(['type' => $validGameTypes[rand(0, 4)]]);

            $fakePlayer = (new Player())
                ->setServer($team->getServer())
                ->setTeam($team)
                ->setIsRealPlayer(false)
                ->setContractSalary(PlayerService::DRAFT_PICK_SALARY_A_GAME)
                ->setContractYears(rand(1, 4))
                ->setUser(null)
                ->setHeight(rand($minHeight, $maxHeight))
                ->setWeight(rand($minWeight, $maxWeight))
                ->setBorn($this->faker->dateTimeBetween('-30 years', '-18 years'))
                ->setCountry($country)
                ->setFirstname($this->faker->firstNameMale())
                ->setLastname($this->faker->lastName())
                ->setPosition($playerPosition)
                ->setFirstType($firstGameType)
                ->setMoney(0)
                ->setSecondType($secondGameType);

            self::setFakePlayerJerseyNumber($team, $fakePlayer);

            $this->entityManager->persist($fakePlayer);
            $this->entityManager->flush();

            self::createFakePlayerStatsOnPlayerCreate($fakePlayer);
            self::createFakePlayerAttributes($fakePlayer);
        }
    }

    /**
     * @param Team $team
     * @param Player $player
     * @param bool $flush
     */
    public function setFakePlayerJerseyNumber(Team $team, Player $player, bool $flush = true)
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createFakePlayerStatsOnPlayerCreate(Player $player)
    {
        if(self::checkIfActiveSeasonExists($player->getServer())) {
            $playerStats = new PlayerStats();

            $playerStats->setGamesPlayed(0);
            $playerStats->setAssists(0);
            $playerStats->setBlocks(0);
            $playerStats->setPlayer($player);
            $playerStats->setPoints(0);
            $playerStats->setRebounds(0);
            $playerStats->setSteals(0);
            $playerStats->setSeason(self::getActiveSeason($player->getServer()));

            $this->entityManager->persist($playerStats);
            $this->entityManager->flush();
        } else {
            self::createNewSeasonWithoutReturn($player->getServer());
        }
    }

    public function createFakePlayerAttributes(Player $player)
    {
        $attributeRepository = $this->entityManager->getRepository(Attribute::class);
        $attributes = $attributeRepository->findAll();

        /**
         * @var Attribute $attribute
         */
        foreach($attributes as $attribute) {
            $playerAttribute = (new PlayerAttribute())
                ->setValue($this->faker->numberBetween(25, $this->faker->numberBetween(25, 40)))
                ->setAttribute($attribute)
                ->setPlayer($player);

            $this->entityManager->persist($playerAttribute);
            $this->entityManager->flush();
        }
    }


    /**
     * @param Server $server
     *
     * @return Season
     */
    private function createAndReturnNewSeason(Server $server)
    {
        $newSeason = (new Season())
            ->setStatus(Season::STATUS_PREPARING)
            ->setServer($server)
        ;

        $this->entityManager->persist($newSeason);
        $this->entityManager->flush();

        self::createNewEntities($newSeason);

        return $newSeason;
    }

    /**
     * @param Season $season
     */
    private function createNewEntities(Season $season): void
    {
        $playerStatsEvent = new CreatePlayerStatsEvent($season);
        $this->eventDispatcher->dispatch($playerStatsEvent, CreatePlayerStatsEvent::NAME);

        $teamStatusEvent = new CreateTeamStatusEvent($season);
        $this->eventDispatcher->dispatch($teamStatusEvent, CreateTeamStatusEvent::NAME);
    }
}
