<?php

namespace App\Repository;

use App\Entity\TrainingCamp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TrainingCamp|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrainingCamp|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrainingCamp[]    findAll()
 * @method TrainingCamp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrainingCampRepository extends ServiceEntityRepository
{
    /**
     * TrainingCampRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TrainingCamp::class);
    }

    /**
     * @return QueryBuilder
     */
    public function findAllWithQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('tc');
    }

    // /**
    //  * @return TrainingCamp[] Returns an array of TrainingCamp objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TrainingCamp
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
