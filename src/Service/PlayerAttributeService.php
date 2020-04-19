<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Player;
use App\Factory\Factory\PlayerAttributeFactory;
use App\Repository\PlayerAttributeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PlayerAttribute AS PlayerAttributeEntity;

class PlayerAttributeService
{
    private $playerAttributeRepository;
    private $playerAttributeFactory;
    private $entityManager;

    /**
     * PlayerAttributeService constructor.
     *
     * @param PlayerAttributeRepository $playerAttributeRepository
     * @param PlayerAttributeFactory $playerAttributeFactory
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        PlayerAttributeRepository $playerAttributeRepository,
        PlayerAttributeFactory $playerAttributeFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->playerAttributeRepository = $playerAttributeRepository;
        $this->playerAttributeFactory    = $playerAttributeFactory;
        $this->entityManager             = $entityManager;
    }

    /**
     * @param int  $playerId
     * @param bool $userPlayer
     *
     * @return array|null
     *
     * @throws \Exception
     */
    public function getPlayerPlayerAttributes(int $playerId, bool $userPlayer = false): ?array
    {
        $attributes = $this->playerAttributeRepository->findPlayerAttributeData($playerId);
        $player = $this->entityManager->getRepository(Player::class)->find($playerId);

        return $this->playerAttributeFactory->create($attributes, $player, $userPlayer);
    }

    /**
     * @param Player $player
     * @param int $attributeId
     *
     * @return PlayerAttributeEntity|null
     */
    public function getPlayerPlayerAttribute(Player $player, int $attributeId): ?PlayerAttributeEntity
    {
        return $this->playerAttributeRepository->findOneBy(['id' => $attributeId, 'player' => $player]);
    }

    /**
     * @param PlayerAttributeEntity $playerAttribute
     * @param float $price
     */
    public function improvePlayerPlayerAttribute(PlayerAttributeEntity $playerAttribute, float $price): void
    {
        $player = $playerAttribute->getPlayer();

        $playerAttribute->setValue($playerAttribute->getValue() + 1);

        $this->entityManager->persist($playerAttribute);
        $this->entityManager->flush();

        if($playerAttribute->getValue() > 99) {
            $this->fixPlayerPlayerAttributeValue($playerAttribute);
        }

        $player->setMoney($player->getMoney() - $price);

        $this->entityManager->persist($player);
        $this->entityManager->flush();
    }

    /**
     * @param PlayerAttributeEntity $playerAttribute
     */
    public function fixPlayerPlayerAttributeValue(PlayerAttributeEntity $playerAttribute): void
    {
        $playerAttribute->setValue(99);

        $this->entityManager->persist($playerAttribute);
        $this->entityManager->flush();
    }
}