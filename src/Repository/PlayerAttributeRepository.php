<?php

namespace App\Repository;

use App\Entity\PlayerAttribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PlayerAttribute|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerAttribute|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerAttribute[]    findAll()
 * @method PlayerAttribute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerAttributeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PlayerAttribute::class);
    }

    /**
     * @param int $playerId
     *
     * @return array|null
     */
    public function findPlayerAttributeData(int $playerId): ?array
    {
        return $this->createQueryBuilder('pa')
            ->select('pa.id AS attributeId, a.name AS attributeName, pa.value AS attributeLevel, gt.name AS gameTypeName')
            ->leftJoin('pa.attribute', 'a')
            ->leftJoin('a.gameType', 'gt')
            ->where('pa.player = :player')
            ->setParameter('player', $playerId)
            ->getQuery()
            ->getArrayResult();
    }
}
