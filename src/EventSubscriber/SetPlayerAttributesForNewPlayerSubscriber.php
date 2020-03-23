<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Attribute;
use App\Entity\PlayerAttribute;
use App\Event\SetPlayerAttributesForNewPlayerEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SetPlayerAttributesForNewPlayerSubscriber implements EventSubscriberInterface
{
    private $entityManager;

    /**
     * SetPlayerAttributesForNewPlayerSubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SetPlayerAttributesForNewPlayerEvent::NAME => [
                ['setPlayerAttributesForNewPlayer']
            ]
        ];
    }

    /**
     * @param SetPlayerAttributesForNewPlayerEvent $event
     */
    public function setPlayerAttributesForNewPlayer(SetPlayerAttributesForNewPlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $attributeRepository = $this->entityManager->getRepository(Attribute::class);
        $attributes = $attributeRepository->findAll();

        /**
         * @var Attribute $attribute
         */
        foreach($attributes as $attribute) {
            $playerAttribute = (new PlayerAttribute())
                ->setValue($attribute->getDefaultValue())
                ->setAttribute($attribute)
                ->setPlayer($player);

            $this->entityManager->persist($playerAttribute);
            $this->entityManager->flush();
        }
    }
}
