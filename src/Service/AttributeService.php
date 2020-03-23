<?php


namespace App\Service;

use App\Entity\Attribute;
use App\Entity\Player;
use App\Entity\PlayerAttribute;
use App\Entity\Position;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class AttributeService
 *
 * @package App\Service
 */
class AttributeService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * AttributeService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param Player $player
     */
    public function createAttributesForPlayer(Player $player)
    {
        $attributeRepository = $this->em->getRepository(Attribute::class);
        $attributes = $attributeRepository->findAll();

        /**
         * @var Attribute $attribute
         */
        foreach($attributes as $attribute) {
            $playerAttribute = (new PlayerAttribute())
                ->setValue($attribute->getDefaultValue())
                ->setAttribute($attribute)
                ->setPlayer($player);

            $this->em->persist($playerAttribute);
            $this->em->flush();
        }
    }
}
