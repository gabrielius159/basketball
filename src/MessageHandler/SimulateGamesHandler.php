<?php

namespace App\MessageHandler;

use App\Entity\GameDay;
use App\Entity\GameDayScores;
use App\Entity\GameType;
use App\Entity\Player;
use App\Entity\PlayerAttribute;
use App\Entity\PlayerBadge;
use App\Entity\Season;
use App\Entity\Server;
use App\Entity\Team;
use App\Message\CheckContracts;
use App\Message\SetChampions;
use App\Message\SetSeasonRewards;
use App\Message\SimulateGames;
use App\Model\PlayerScore;
use App\Repository\GameDayRepository;
use App\Repository\SeasonRepository;
use App\Repository\ServerRepository;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class SimulateGamesHandler
 *
 * @package App\MessageHandler
 */
class SimulateGamesHandler implements MessageHandlerInterface
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
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var Generator
     */
    protected $faker;

    /**
     * @var ServerRepository
     */
    private $serverRepository;

    /**
     * @var SeasonRepository
     */
    private $seasonRepository;

    /**
     * @var GameDayRepository
     */
    private $gameDayRepository;

    /**
     * SimulateGamesHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SeasonService $seasonService
     * @param MessageBusInterface $messageBus
     * @param ServerRepository $serverRepository
     * @param SeasonRepository $seasonRepository
     * @param GameDayRepository $gameDayRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SeasonService $seasonService,
        MessageBusInterface $messageBus,
        ServerRepository $serverRepository,
        SeasonRepository $seasonRepository,
        GameDayRepository $gameDayRepository
    ) {
        $this->entityManager = $entityManager;
        $this->seasonService = $seasonService;
        $this->messageBus = $messageBus;
        $this->serverRepository = $serverRepository;
        $this->seasonRepository = $seasonRepository;
        $this->gameDayRepository = $gameDayRepository;
        $this->faker = Factory::create();
    }

    /**
     * @param SimulateGames $message
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function __invoke(SimulateGames $message)
    {
        /**
         * @var Season $season
         * @var Server $server
         */
        $season = $this->seasonRepository->find($message->getSeasonId());
        $server = $this->serverRepository->find($message->getServerId());

        $gameDays = $this->gameDayRepository->findBy([
            'season' => $season,
            'status' => GameDay::STATUS_WAITING
        ]);

        if($gameDays) {
            /**
             * @var GameDay $gameDay
             */
            foreach($gameDays as $gameDay) {
                $this->playGame($gameDay->getId());
            }
        }

        $season->setStatus(Season::STATUS_FINISHED);

        $this->entityManager->flush();

        $this->messageBus->dispatch(new SetChampions($season->getId()));
        $this->messageBus->dispatch(new SetSeasonRewards($season->getId()));
        $this->messageBus->dispatch(new CheckContracts($server->getId(), $season->getId() + 1));
        $this->seasonService->createNewSeasonWithoutReturn($server);
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
     * @param int $gameDayId
     */
    public function playGame(int $gameDayId)
    {
        /**
         * @var GameDay $gameDay
         */
        $gameDay = $this->gameDayRepository->find($gameDayId);

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
                $stealRange['all']['max'] += 0.02;
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
                $blockRange['all']['max'] += 0.02;
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
                $stealRange['all']['max'] += 0.02;
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
                $blockRange['all']['max'] += 0.02;
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
}
