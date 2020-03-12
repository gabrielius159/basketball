<?php

namespace App\Repository;

use App\Entity\GameDayScores;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GameDayScores|null find($id, $lockMode = null, $lockVersion = null)
 * @method GameDayScores|null findOneBy(array $criteria, array $orderBy = null)
 * @method GameDayScores[]    findAll()
 * @method GameDayScores[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameDayScoresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameDayScores::class);
    }

    // /**
    //  * @return GameDayScores[] Returns an array of GameDayScores objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GameDayScores
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
