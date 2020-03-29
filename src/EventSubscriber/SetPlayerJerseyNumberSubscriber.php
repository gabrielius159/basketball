<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\SetPlayerJerseyNumberEvent;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SetPlayerJerseyNumberSubscriber implements EventSubscriberInterface
{
    private $playerRepository;
    private $entityManager;

    /**
     * SetPlayerJerseyNumberSubscriber constructor.
     *
     * @param PlayerRepository       $playerRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(PlayerRepository $playerRepository, EntityManagerInterface $entityManager)
    {
        $this->playerRepository = $playerRepository;
        $this->entityManager    = $entityManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SetPlayerJerseyNumberEvent::NAME => [
                ['setPlayerJerseyNumber']
            ]
        ];
    }

    /**
     * @param SetPlayerJerseyNumberEvent $event
     */
    public function setPlayerJerseyNumber(SetPlayerJerseyNumberEvent $event): void
    {
        $jerseyNumber = null;
        $team = $event->getTeam();
        $player = $event->getPlayer();
        $usedJerseys = $this->playerRepository->getTakenJerseyNumbers($team);

        while(is_null($jerseyNumber)) {
            $generatedNumber = rand(0, 99);

            if(!in_array($generatedNumber, $usedJerseys)) {
                $jerseyNumber = $generatedNumber;
            }
        }

        $player->setJerseyNumber($jerseyNumber);

        $this->entityManager->persist($player);
        $this->entityManager->flush();
    }
}
