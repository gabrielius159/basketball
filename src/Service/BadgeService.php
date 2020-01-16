<?php

namespace App\Service;

use App\Entity\Badge;
use App\Entity\Player;
use App\Entity\PlayerBadge;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class BadgeService
 *
 * @package App\Service
 */
class BadgeService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * BadgeService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id
     *
     * @return Badge|null
     */
    public function findOneById(int $id)
    {
        /**
         * @var Badge $badge
         */
        $badge = $this->entityManager->getRepository(Badge::class)->find($id);

        return $badge;
    }

    /**
     * @param Player $player
     * @param Badge $badge
     */
    public function createBadgeForPlayer(Player $player, Badge $badge)
    {
        $playerHasThisBadge = false;

        foreach($player->getPlayerBadges() as $playerBadge) {
            if($playerBadge->getBadge()->getName() === $badge->getName()) {
                $playerHasThisBadge = true;

                break;
            }
        }

        if(!$playerHasThisBadge) {
            $badge = (new PlayerBadge())
                ->setPlayer($player)
                ->setBadge($badge)
            ;

            $this->entityManager->persist($badge);
            $this->entityManager->flush();
        }
    }
}
