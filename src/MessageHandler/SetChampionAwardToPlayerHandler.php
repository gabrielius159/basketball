<?php

namespace App\MessageHandler;

use App\Entity\PlayerAward;
use App\Entity\Season;
use App\Entity\Team;
use App\Message\SetChampionAwardToPlayer;
use App\Utils\Award;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class SetChampionAwardToPlayerHandler
 *
 * @package App\MessageHandler
 */
class SetChampionAwardToPlayerHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * SetChampionAwardToPlayerHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param SetChampionAwardToPlayer $message
     */
    public function __invoke(SetChampionAwardToPlayer $message)
    {
        $team = $this->entityManager->getRepository(Team::class)->find($message->getTeamId());
        $season = $this->entityManager->getRepository(Season::class)->find($message->getSeasonId());

        foreach($team->getPlayers() as $player) {
            $player->setMoney($player->getMoney() + Award::CHAMPION_REWARD);

            $playerAward = (new PlayerAward())
                ->setAward(Award::PLAYER_CHAMPION)
                ->setSeason($season)
                ->setPlayer($player)
            ;

            $this->entityManager->persist($playerAward);
            $this->entityManager->flush();
        }
    }
}
