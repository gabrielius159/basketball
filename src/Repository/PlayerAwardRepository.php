<?php

namespace App\Repository;

use App\Entity\PlayerAward;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PlayerAward|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerAward|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerAward[]    findAll()
 * @method PlayerAward[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerAwardRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PlayerAward::class);
    }

    // /**
    //  * @return PlayerAward[] Returns an array of PlayerAward objects
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
    public function findOneBySomeField($value): ?PlayerAward
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
