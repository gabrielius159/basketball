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

    // /**
    //  * @return PlayerAttribute[] Returns an array of PlayerAttribute objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PlayerAttribute
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
