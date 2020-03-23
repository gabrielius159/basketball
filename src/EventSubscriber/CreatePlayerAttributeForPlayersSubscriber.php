<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\PlayerAttribute;
use App\Event\CreatePlayerAttributeForPlayersEvent;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreatePlayerAttributeForPlayersSubscriber implements EventSubscriberInterface
{
    const BATCH_SIZE = 20;

    private $playerRepository;
    private $entityManager;

    /**
     * CreatePlayerAttributeForPlayersSubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param PlayerRepository       $playerRepository
     */
    public function __construct(EntityManagerInterface $entityManager, PlayerRepository $playerRepository)
    {
        $this->entityManager = $entityManager;
        $this->playerRepository = $playerRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CreatePlayerAttributeForPlayersEvent::NAME => [
                ['createPlayerAttributeForPlayers']
            ]
        ];
    }

    /**
     * @param CreatePlayerAttributeForPlayersEvent $event
     */
    public function createPlayerAttributeForPlayers(CreatePlayerAttributeForPlayersEvent $event): void
    {
        $i = 1;

        $attribute = $event->getAttribute();
        $playersQuery = $this->playerRepository->findAllPlayers(true);
        $iterableResult = $playersQuery->iterate();

        foreach ($iterableResult as $row) {
            $player = $row[0];

            $playerAttribute = (new PlayerAttribute())
                ->setPlayer($player)
                ->setAttribute($attribute)
                ->setValue($attribute->getDefaultValue());

            $this->entityManager->persist($playerAttribute);

            if (($i % self::BATCH_SIZE) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear(PlayerAttribute::class);
            }

            ++$i;
        }

        $this->entityManager->flush();
    }
}