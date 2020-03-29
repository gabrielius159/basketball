<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Constant\PlayerConstants;
use App\Entity\Attribute;
use App\Entity\Country;
use App\Entity\GameType;
use App\Entity\Player;
use App\Entity\PlayerAttribute;
use App\Entity\PlayerStats;
use App\Entity\Position;
use App\Entity\Team;
use App\Event\GenerateFakePlayersEvent;
use App\Event\SetPlayerJerseyNumberEvent;
use App\Repository\TeamRepository;
use App\Service\PlayerService;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class GenerateFakePlayersSubscriber implements EventSubscriberInterface
{
    const MIN_PLAYERS_FOR_EACH_POSITION = 2;

    private $entityManager;
    private $seasonService;
    private $teamRepository;
    private $eventDispatcher;
    private $faker;

    /**
     * GenerateFakePlayersSubscriber constructor.
     *
     * @param EntityManagerInterface   $entityManager
     * @param SeasonService            $seasonService
     * @param TeamRepository           $teamRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SeasonService $seasonService,
        TeamRepository $teamRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager   = $entityManager;
        $this->seasonService   = $seasonService;
        $this->teamRepository  = $teamRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->faker = Factory::create();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            GenerateFakePlayersEvent::NAME => [
                ['generateFakePlayers']
            ]
        ];
    }

    /**
     * @param GenerateFakePlayersEvent $event
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function generateFakePlayers(GenerateFakePlayersEvent $event): void
    {
        $server = $event->getServer();
        $teams = $this->teamRepository->findBy(['server' => $server]);

        foreach ($teams as $team) {
            $pg = $this->teamRepository->findCountOfPositionByServerAndTeamId(
                $server, $team, Position::POINT_GUARD
            );
            $sg = $this->teamRepository->findCountOfPositionByServerAndTeamId(
                $server, $team, Position::SHOOTING_GUARD
            );
            $sf = $this->teamRepository->findCountOfPositionByServerAndTeamId(
                $server, $team, Position::SMALL_FORWARD
            );
            $pf = $this->teamRepository->findCountOfPositionByServerAndTeamId(
                $server, $team, Position::POWER_FORWARD
            );
            $c = $this->teamRepository->findCountOfPositionByServerAndTeamId(
                $server, $team, Position::CENTER
            );

            if ($pg < self::MIN_PLAYERS_FOR_EACH_POSITION) {
                $this->createFakePlayer($team, Position::POINT_GUARD, (self::MIN_PLAYERS_FOR_EACH_POSITION - $pg));
            }

            if ($sg < self::MIN_PLAYERS_FOR_EACH_POSITION) {
                $this->createFakePlayer($team, Position::SHOOTING_GUARD, (self::MIN_PLAYERS_FOR_EACH_POSITION - $sg));
            }

            if ($sf < self::MIN_PLAYERS_FOR_EACH_POSITION) {
                $this->createFakePlayer($team, Position::SMALL_FORWARD, (self::MIN_PLAYERS_FOR_EACH_POSITION - $sf));
            }

            if ($pf < self::MIN_PLAYERS_FOR_EACH_POSITION) {
                $this->createFakePlayer($team, Position::POWER_FORWARD, (self::MIN_PLAYERS_FOR_EACH_POSITION - $pf));
            }

            if ($c < self::MIN_PLAYERS_FOR_EACH_POSITION) {
                $this->createFakePlayer($team, Position::CENTER, (self::MIN_PLAYERS_FOR_EACH_POSITION - $c));
            }
        }
    }

    /**
     * @param Team   $team
     * @param string $position
     * @param int    $iterations
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function createFakePlayer(Team $team, string $position, int $iterations = 1): void
    {
        $minHeight = PlayerConstants::PLAYER_MAX_MIN_WEIGHTS_AND_HEIGHTS_BY_POSITION[$position][PlayerConstants::MIN_HEIGHT];
        $maxHeight = PlayerConstants::PLAYER_MAX_MIN_WEIGHTS_AND_HEIGHTS_BY_POSITION[$position][PlayerConstants::MAX_HEIGHT];
        $minWeight = PlayerConstants::PLAYER_MAX_MIN_WEIGHTS_AND_HEIGHTS_BY_POSITION[$position][PlayerConstants::MIN_WEIGHT];
        $maxWeight = PlayerConstants::PLAYER_MAX_MIN_WEIGHTS_AND_HEIGHTS_BY_POSITION[$position][PlayerConstants::MAX_WEIGHT];

        for ($i = 0; $i < $iterations; $i++) {
            $country = $this->entityManager
                ->getRepository(Country::class)
                ->find(rand(1, 249));

            $playerPosition = $this->entityManager
                ->getRepository(Position::class)
                ->findOneBy([
                    'name' => $position
                ]);

            $firstGameType = $this->entityManager
                ->getRepository(GameType::class)
                ->findOneBy(['type' => PlayerConstants::VALID_GAME_TYPES[rand(0, 4)]]);

            $secondGameType = $this->entityManager
                ->getRepository(GameType::class)
                ->findOneBy(['type' => PlayerConstants::VALID_GAME_TYPES[rand(0, 4)]]);

            $fakePlayer = (new Player())
                ->setServer($team->getServer())
                ->setTeam($team)
                ->setIsRealPlayer(false)
                ->setContractSalary((float) rand(PlayerService::DRAFT_PICK_SALARY_A_GAME, 1000))
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

            $this->entityManager->persist($fakePlayer);
            $this->entityManager->flush();

            $event = new SetPlayerJerseyNumberEvent($fakePlayer, $team);
            $this->eventDispatcher->dispatch($event, SetPlayerJerseyNumberEvent::NAME);

            $this->createFakePlayerStatsOnPlayerCreate($fakePlayer);
            $this->createFakePlayerAttributes($fakePlayer);
        }
    }

    /**
     * @param Player $player
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function createFakePlayerStatsOnPlayerCreate(Player $player): void
    {
        if($this->seasonService->checkIfActiveSeasonExists($player->getServer())) {
            $playerStats = new PlayerStats();

            $playerStats->setGamesPlayed(0);
            $playerStats->setAssists(0);
            $playerStats->setBlocks(0);
            $playerStats->setPlayer($player);
            $playerStats->setPoints(0);
            $playerStats->setRebounds(0);
            $playerStats->setSteals(0);
            $playerStats->setSeason($this->seasonService->getActiveSeason($player->getServer()));

            $this->entityManager->persist($playerStats);
            $this->entityManager->flush();
        } else {
            $this->seasonService->createNewSeasonWithoutReturn($player->getServer());
        }
    }

    private function createFakePlayerAttributes(Player $player): void
    {
        $attributeRepository = $this->entityManager->getRepository(Attribute::class);
        $attributes = $attributeRepository->findAll();

        foreach($attributes as $attribute) {
            $playerAttribute = (new PlayerAttribute())
                ->setValue($this->faker->numberBetween(25, $this->faker->numberBetween(25, 40)))
                ->setAttribute($attribute)
                ->setPlayer($player);

            $this->entityManager->persist($playerAttribute);
            $this->entityManager->flush();
        }
    }
}
