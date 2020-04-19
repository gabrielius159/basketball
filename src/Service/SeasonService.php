<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\GameDay;
use App\Entity\GameDayScores;
use App\Entity\GameType;
use App\Entity\Player;
use App\Entity\PlayerAttribute;
use App\Entity\PlayerAward;
use App\Entity\PlayerBadge;
use App\Entity\PlayerStats;
use App\Entity\Season;
use App\Entity\Server;
use App\Entity\Team;
use App\Entity\TeamAward;
use App\Event\CreatePlayerStatsEvent;
use App\Event\CreateTeamStatusEvent;
use App\Model\PlayerScore;
use App\Repository\GameDayRepository;
use App\Repository\PlayerRepository;
use App\Repository\PlayerStatsRepository;
use App\Repository\SeasonRepository;
use App\Repository\TeamRepository;
use App\Repository\TeamStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SeasonService
{
    private $seasonRepository;
    private $playerStatsRepository;
    private $entityManager;
    private $gameDayRepository;
    private $faker;
    private $playerRepository;
    private $teamStatusRepository;
    private $eventDispatcher;
    private $teamRepository;

    /**
     * SeasonService constructor.
     *
     * @param SeasonRepository         $seasonRepository
     * @param PlayerStatsRepository    $playerStatsRepository
     * @param EntityManagerInterface   $entityManager
     * @param GameDayRepository        $gameDayRepository
     * @param PlayerRepository         $playerRepository
     * @param TeamStatusRepository     $teamStatusRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        SeasonRepository $seasonRepository,
        PlayerStatsRepository $playerStatsRepository,
        EntityManagerInterface $entityManager,
        GameDayRepository $gameDayRepository,
        PlayerRepository $playerRepository,
        TeamStatusRepository $teamStatusRepository,
        EventDispatcherInterface $eventDispatcher,
        TeamRepository $teamRepository
    ) {
        $this->seasonRepository = $seasonRepository;
        $this->playerStatsRepository = $playerStatsRepository;
        $this->entityManager = $entityManager;
        $this->gameDayRepository = $gameDayRepository;
        $this->playerRepository = $playerRepository;
        $this->teamStatusRepository = $teamStatusRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->teamRepository = $teamRepository;
        $this->faker = Factory::create();
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
            $season = $this->createAndReturnNewSeason($server);
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

            $this->createNewEntities($newSeason);
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
        $season = $this->getActiveSeason($server);

        if($season instanceof Season) {
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
     * @param string $awardType
     *
     * @return Player|null
     */
    public function getLastSeasonAwardWinnerByAwardType(Season $season, string $awardType): ?Player
    {
        $lastSeason = $this->entityManager->getRepository(Season::class)->find($season->getId() -1);

        if (!$lastSeason instanceof Season) {
            return null;
        }

        $playerAward = $this->entityManager->getRepository(PlayerAward::class)->findOneBy([
            'season' => $lastSeason,
            'award' => $awardType
        ]);

        if (!$playerAward instanceof PlayerAward) {
            return null;
        }

        $player = $playerAward->getPlayer();

        if (!$player instanceof Player) {
            return null;
        }

        return $player;
    }

    /**
     * @param Server $server
     *
     * @return bool
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function teamsHaveCoach(Server $server): bool
    {
        $teamsWithoutCoach = $this->teamRepository->findCountOfTeamsWithoutCoach($server);

        return $teamsWithoutCoach === 0;
    }

    /**
     * @param string $status
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getSeasonStatusName(string $status): string
    {
        if (!array_key_exists($status, Season::STATUS_NAME)) {
            throw new \Exception('Unknown season status.');
        }

        return Season::STATUS_NAME[$status];
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

        if($team instanceof Team) {
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

            $playerScore->setPoints($points < 0 ? 0 : (int) $points);

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

            $playerScore->setAssists((int) $assists);

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

            $playerScore->setRebounds((int) $rebounds);

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

            $playerScore->setSteals((int) $steals);

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

            $playerScore->setBlocks((int) $blocks);


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


            $playerScore->setPoints($points < 0 ? 0 : (int) $points);

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

            $playerScore->setAssists((int) $assists);

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


            $playerScore->setRebounds((int) $rebounds);

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
            $playerScore->setSteals((int) $steals);

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
            $playerScore->setBlocks((int) $blocks);


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
        return $this->gameDayRepository->getByDate(new \DateTime(), $this->getActiveSeason($server));
    }

    /**
     * @param Server $server
     *
     * @return array
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTwoUpcomingGames(Server $server): array
    {
        $games = $this->gameDayRepository->getTwoUpcomingGames($this->getActiveSeason($server));

        if (count($games) === 0) {
            return [];
        }

        return $games;
    }

    /**
     * @param Server $server
     *
     * @return Season
     */
    private function createAndReturnNewSeason(Server $server): Season
    {
        $newSeason = (new Season())
            ->setStatus(Season::STATUS_PREPARING)
            ->setServer($server)
        ;

        $this->entityManager->persist($newSeason);
        $this->entityManager->flush();

        $this->createNewEntities($newSeason);

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
