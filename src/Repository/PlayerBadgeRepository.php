<?php

namespace App\Repository;

use App\Entity\PlayerBadge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PlayerBadge|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerBadge|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerBadge[]    findAll()
 * @method PlayerBadge[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerBadgeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerBadge::class);
    }

    // /**
    //  * @return PlayerBadge[] Returns an array of PlayerBadge objects
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
    public function findOneBySomeField($value): ?PlayerBadge
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
