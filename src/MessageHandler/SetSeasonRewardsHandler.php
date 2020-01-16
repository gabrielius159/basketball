<?php

namespace App\MessageHandler;

use App\Entity\PlayerAward;
use App\Entity\Season;
use App\Message\SetSeasonRewards;
use App\Repository\PlayerRepository;
use App\Repository\PlayerStatsRepository;
use App\Utils\Award;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class SetSeasonRewardsHandler
 *
 * @package App\MessageHandler
 */
class SetSeasonRewardsHandler implements MessageHandlerInterface
{
    /**
     * @var PlayerStatsRepository
     */
    private $playerStatsRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * SetSeasonRewardsHandler constructor.
     *
     * @param PlayerStatsRepository $playerStatsRepository
     * @param EntityManagerInterface $entityManager
     * @param PlayerRepository $playerRepository
     */
    public function __construct(
        PlayerStatsRepository $playerStatsRepository,
        EntityManagerInterface $entityManager,
        PlayerRepository $playerRepository
    ) {
        $this->playerStatsRepository = $playerStatsRepository;
        $this->entityManager = $entityManager;
        $this->playerRepository = $playerRepository;
    }

    /**
     * @param SetSeasonRewards $message
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function __invoke(SetSeasonRewards $message)
    {
        /**
         * @var Season $season
         */
        $season = $this->entityManager->getRepository(Season::class)->find($message->getSeasonId());

        $mvp = $this->playerStatsRepository->getSeasonMVP($season)->getPlayer();

        $mvpAward = (new PlayerAward())
            ->setPlayer($mvp)
            ->setSeason($season)
            ->setAward(Award::PLAYER_MVP)
        ;

        $this->entityManager->persist($mvpAward);
        $this->entityManager->flush();

        $mvp->setMoney($mvp->getMoney() + Award::MVP_REWARD);

        $this->entityManager->persist($mvp);
        $this->entityManager->flush();

        $dpoy = $this->playerStatsRepository->getSeasonDPOY($season)->getPlayer();

        $dpoyAward = (new PlayerAward())
            ->setAward(Award::PLAYER_DPOY)
            ->setSeason($season)
            ->setPlayer($dpoy)
        ;

        $this->entityManager->persist($dpoyAward);
        $this->entityManager->flush();

        $dpoy->setMoney($dpoy->getMoney() + Award::DPOY_REWARD);

        $this->entityManager->persist($dpoy);
        $this->entityManager->flush();

        $roty = $this->playerRepository->getSeasonROTY($season);

        if($roty) {
            $rotyAward = (new PlayerAward())
                ->setAward(Award::PLAYER_ROTY)
                ->setSeason($season)
                ->setPlayer($roty)
            ;

            $this->entityManager->persist($rotyAward);
            $this->entityManager->flush();

            $roty->setMoney($roty->getMoney() + Award::ROTY_REWARD);

            $this->entityManager->persist($roty);
            $this->entityManager->flush();
        }
    }
}
