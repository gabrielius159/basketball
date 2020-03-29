<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\PlayerBadge;
use App\Event\CreateBadgeForPlayerEvent;
use App\Repository\PlayerBadgeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateBadgeForPlayerSubscriber implements EventSubscriberInterface
{
    private $playerBadgeRepository;
    private $entityManager;

    /**
     * CreateBadgeForPlayerSubscriber constructor.
     *
     * @param PlayerBadgeRepository  $playerBadgeRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        PlayerBadgeRepository $playerBadgeRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->playerBadgeRepository = $playerBadgeRepository;
        $this->entityManager         = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CreateBadgeForPlayerEvent::NAME => [
                ['createBadgeForPlayer']
            ]
        ];
    }

    /**
     * @param CreateBadgeForPlayerEvent $event
     */
    public function createBadgeForPlayer(CreateBadgeForPlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $badge = $event->getBadge();

        $playerHasThisBadge = $this->playerBadgeRepository->findOneBy([
            'player' => $player,
            'badge' => $badge
        ]);

        if (!$playerHasThisBadge instanceof PlayerBadge) {
            $badge = (new PlayerBadge())
                ->setPlayer($player)
                ->setBadge($badge)
            ;

            $this->entityManager->persist($badge);
            $this->entityManager->flush();
        }
    }
}