<?php

namespace App\Repository;

use App\Entity\UserReward;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserReward|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserReward|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserReward[]    findAll()
 * @method UserReward[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRewardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserReward::class);
    }

    // /**
    //  * @return UserReward[] Returns an array of UserReward objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserReward
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
