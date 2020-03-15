<?php

namespace App\Repository;

use App\Entity\Server;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    /**
     * TeamRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /**
     * @return QueryBuilder
     */
    public function findAllWithQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.id')
            ;
    }

    /**
     * @param Server $server
     * @param bool   $query
     *
     * @return \Doctrine\ORM\Query|mixed
     */
    public function findTeamsByServer(Server $server, bool $query = false)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.server = :server')
            ->setParameter('server', $server)
            ->getQuery();

        if (!$query) {
            return $qb->getResult();
        }

        return $qb;
    }

    /**
     * @param Server $server
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findCountOfTeamsWithoutCoach(Server $server): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->leftJoin('t.coach', 'c', 'WITH', 'c.team = t.id')
            ->where('c IS NULL')
            ->andWhere('t.server = :server')
            ->setParameter('server', $server)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
