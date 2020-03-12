<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param string|null $keyword
     *
     * @return QueryBuilder
     */
    public function findAllWithQueryBuilder(?string $keyword): QueryBuilder
    {
        if($keyword) {
            return $this->createQueryBuilder('u')
                ->where('u.email LIKE :keyword')
                ->orderBy('u.email', 'ASC')
                ->setParameter('keyword', '%' . $keyword . '%')
                ;
        }

        return $this->createQueryBuilder('u')
            ->orderBy('u.email', 'ASC');
    }

    /**
     * @param string $keyword
     *
     * @return QueryBuilder
     */
    public function findAllWithQueryBuilderAndKeyword(string $keyword): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->where('u.email LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%');
    }

    // /**
    //  * @return User[] Returns an array of User objects
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
    public function findOneBySomeField($value): ?User
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
